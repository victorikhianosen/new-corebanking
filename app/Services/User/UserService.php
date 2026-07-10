<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Message\MessageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private AuditService $audit,
        private MessageService $message,

    ) {}

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()->latest()->paginate($perPage)->withQueryString();
    }

    public function find(int $id): User
    {
        return User::findOrFail($id);
    }



    public function create(array $data): User
    {
        $plainPassword = $data['password'];

        $data['code']     = $this->generateUniqueCode();
        $data['username'] = $data['username'] ?? $this->generateUniqueUsername($data['first_name'], $data['last_name']);
        $data['password'] = bcrypt($plainPassword);
        $data['status']   = 'pending';

        $user = User::create($data);

        // Deferred: audit log
        dispatch(function () use ($user) {
            $this->audit->log(
                action: 'created',
                module: 'users',
                auditable: $user,
                after: $user->only(['id', 'username', 'code', 'staff_code', 'email']),
                description: "New user account '{$user->username}' was created with code '{$user->code}' and staff code '{$user->staff_code}'.",
            );
        })->afterResponse();

        dispatch(function () use ($user, $plainPassword) {
            $this->message->send(
                actor: $user,
                channel: 'email',
                type: 'account_created',
                recipient: $user->email,
                body: view('emails.bodies.user.created', [
                    'user'     => $user,
                    'username' => $user->username,
                    'password' => $plainPassword,
                ])->render(),
                subject: 'Welcome to ' . config('app.name') . ' — Your Account is Ready',
                payload: ['user_id' => $user->id, 'code' => $user->code],
            );
        })->afterResponse();

        return $user;
    }


    public function update(int $id, array $data): User
    {
        $user = $this->find($id);

        $trackedFields = ['username', 'email', 'branch_id', 'phone'];
        $before = $user->only($trackedFields);

        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = bcrypt($data['password']);
        }

        $filteredData = array_filter($data, fn($value) => $value !== null && $value !== '');

        $user->update($filteredData);
        $user->refresh();

        $this->audit->log(
            action: 'updated',
            module: 'users',
            auditable: $user,
            before: $before,
            after: $user->only($trackedFields),
            description: "User '{$user->username}' ({$user->code}) updated.",
        );

        return $user;
    }

    public function updateStatus(int $id, string $status): User
    {
        $user = $this->find($id);
        $before = $user->status;
        $user->update(['status' => $status]);
        $user->refresh();

        $this->audit->log(
            action: 'status_updated',
            module: 'users',
            auditable: $user,
            before: ['status' => $before],
            after: ['status' => $user->status],
            description: "User '{$user->username}' ({$user->code}) status changed from '{$before}' to '{$user->status}'.",
        );

        return $user;
    }

    private function generateUniqueCode(): string
    {
        $next = User::count() + 1;

        do {
            $code = 'USR' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (User::where('code', $code)->exists());

        return $code;
    }

    private function generateUniqueUsername(string $firstName, string $lastName): string
    {
        $base     = Str::slug($firstName . '.' . $lastName, '.');
        $username = $base;
        $suffix   = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $suffix;
            $suffix++;
        }

        return $username;
    }
}
