<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateUserStatusRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserService $users,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->users->list($request->integer('per_page', 15));

        return $this->success(
            message: 'Users retrieved successfully.',
            data: UserResource::collection($users),
        );
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->users->find($id);

        return $this->success(
            message: 'User retrieved successfully.',
            data: new UserResource($user),
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->users->create($request->validated());

            return $this->success(
                message: 'User created successfully.',
                data: new UserResource($user),
                responseCode: '000',
                statusCode: 201,
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'We are unable to process your request please try again.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->users->update($id, $request->validated());

            return $this->success(
                message: 'User updated successfully.',
                data: new UserResource($user),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'We are unable to process your request please try again.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }

    public function updateStatus(UpdateUserStatusRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->users->updateStatus($id, $request->validated()['status']);
            return $this->success(
                message: 'User status updated successfully.',
                data: new UserResource($user),
            );
        } catch (\Throwable $e) {
            report($e);

            return $this->error(
                message: 'We are unable to process your request please try again.',
                responseCode: '100',
                statusCode: 500,
            );
        }
    }
}