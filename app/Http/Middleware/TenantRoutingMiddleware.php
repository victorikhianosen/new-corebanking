<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\Tenant\TenantManagerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantRoutingMiddleware
{
    public function __construct(private TenantManagerService $tenants) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantCode = $request->header('X-Tenant-Id');

        if (! $tenantCode) {
            return $this->fail('X-Tenant-Id header is required.', '401', 401);
        }

        $tenant = Tenant::query()
            ->where('code', $tenantCode)
            ->where('status', 'active')
            ->first();

        if (! $tenant) {
            return $this->fail('Unknown or inactive tenant.', '403', 403);
        }

        $this->tenants->switchTo($tenant);

        try {
            return $next($request);
        } finally {
            $this->tenants->forget();
        }
    }

    private function fail(string $message, string $responseCode, int $statusCode): Response
    {
        return response()->json([
            'status'       => 'error',
            'responseCode' => $responseCode,
            'message'      => $message,
        ], $statusCode);
    }
}