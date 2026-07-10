<?php

namespace App\Services\Audit;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditService
{
    public function log(
        string $action,
        ?string $module = null,
        ?Model $auditable = null,
        ?array $before = null,
        ?array $after = null,
        ?string $description = null,
        ?Model $performer = null,
        ?string $actorType = null,
    ): AuditTrail {
        $actor = $performer ?? auth()->user();

        return AuditTrail::create([
            'actor_type'        => $actorType ?? $this->resolveActorType($actor),
            'performed_by_type' => $actor?->getMorphClass(),
            'performed_by_id'   => $actor?->getKey(),
            'performed_by_name' => $this->resolveName($actor),

            'module'        => $module ?? $this->moduleFrom($auditable),
            'actions'       => $action,
            'description'   => $description,

            'before_change' => $before,
            'after_change'  => $after,

            'ip'            => request()?->ip(),
            'agent'         => request()?->userAgent(),
            'channel'       => $this->channel(),
            'tenant_code'   => $this->tenantCode(),
        ]);
    }

    public function logChanges(string $action, Model $model, ?string $description = null): AuditTrail
    {
        return $this->log(
            action: $action,
            auditable: $model,
            before: $model->getOriginal(),
            after: $model->getChanges(),
            description: $description,
        );
    }

    private function resolveActorType(?Model $actor): string
    {
        if (! $actor) {
            return app()->runningInConsole() ? 'cron' : 'system';
        }

        return match (class_basename($actor)) {
            'Admin' => 'admin',
            'User'  => 'user',
            default => 'user',
        };
    }

    private function resolveName(?Model $actor): ?string
    {
        if (! $actor) {
            return app()->runningInConsole() ? 'System (console)' : 'System';
        }

        return $actor->name ?? $actor->email ?? null;
    }

    private function moduleFrom(?Model $auditable): ?string
    {
        return $auditable
            ? Str::of(class_basename($auditable))->snake()->plural()->value()
            : null;
    }

    private function channel(): string
    {
        $allowed = ['web', 'mobile', 'ussd', 'pos', 'api', 'atm'];

        $channel = Str::lower((string) request()?->header('X-Channel'));

        return in_array($channel, $allowed, true) ? $channel : 'web';
    }

    private function tenantCode(): ?string
    {
        return request()?->attributes->get('tenant')?->code
            ?? request()?->header('X-Tenant-Id');
    }
}