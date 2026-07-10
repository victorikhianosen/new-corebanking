<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantProvisionerService
{
    public function __construct(private TenantManagerService $tenants) {}

    public function create(array $data): Tenant
    {
        $code         = $this->generateUniqueCode();
        $databaseName = $this->generateUniqueDatabaseName($data['name']);

        $databaseHost     = config('database.connections.mysql.host');
        $databasePort     = (int) config('database.connections.mysql.port');
        $databaseUsername = config('database.connections.mysql.username');
        $databasePassword = config('database.connections.mysql.password');

        $tenant = null;

        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `$databaseName`
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $tenant = Tenant::create([
                'code'                => $code,
                'name'                => $data['name'],
                'type'                => $data['type'],
                'email'               => $data['email']   ?? null,
                'phone'               => $data['phone']   ?? null,
                'website'             => $data['website'] ?? null,
                'country'             => $data['country'],
                'state'               => $data['state']   ?? null,
                'city'                => $data['city']    ?? null,
                'address'             => $data['address'] ?? null,
                'database_connection' => 'tenant',
                'database_host'       => $databaseHost,
                'database_port'       => $databasePort,
                'database_name'       => $databaseName,
                'database_username'   => $databaseUsername,
                'database_password'   => $databasePassword,
                'api_secret'          => $this->generateUniqueApiSecret(),
                'timezone'            => 'Africa/Lagos',
                'currency'            => 'NGN',
                'locale'              => 'en',
                'status'              => 'active',
            ]);

            $this->tenants->configure(
                $databaseName,
                $databaseUsername,
                $databasePassword,
                $databaseHost,
                $databasePort
            );

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force'    => true,
            ]);

            $this->tenants->forget();

            return $tenant;
        } catch (\Throwable $e) {
            $this->tenants->forget();

            if ($tenant) {
                $tenant->forceDelete();
            }

            try {
                DB::statement("DROP DATABASE IF EXISTS `$databaseName`");
            } catch (\Throwable $ignore) {
            }

            throw $e;
        }
    }

 public function generateUniqueCode(): string
{
    $next = Tenant::count() + 1;

    do {
        $code = 'TNT' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        $next++;
    } while (Tenant::where('code', $code)->exists());

    return $code;
}

    public function generateUniqueDatabaseName(string $name): string
    {
        $base = 'tenant_' . Str::slug($name, '_');   // "Zenith Bank" -> "tenant_zenith_bank"

        $databaseName = $base;
        $counter      = 1;

        while ($this->databaseExists($databaseName)) {
            $counter++;
            $databaseName = $base . '_' . $counter;   // tenant_zenith_bank_2, _3, ...
        }

        return $databaseName;
    }

    private function databaseExists(string $databaseName): bool
    {
        $result = DB::select(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$databaseName]
        );

        return ! empty($result);
    }

    public function generateUniqueApiSecret(): string
    {
        do {
            $secret = Str::random(48);
        } while (Tenant::where('api_secret', $secret)->exists());

        return $secret;
    }
}