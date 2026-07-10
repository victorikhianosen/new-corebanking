<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantManagerService
{
    public function switchTo(Tenant $tenant): void
    {
        $this->configure(
            $tenant->database_name,
            $tenant->database_username,
            $tenant->database_password,
            $tenant->database_host,
            (int) $tenant->database_port,
        );

        DB::setDefaultConnection('tenant');
    }

    public function configure(
        string $database,
        string $username,
        ?string $password,
        ?string $host = null,
        ?int $port = null,
    ): void {
        Config::set('database.connections.tenant', array_merge(
            config('database.connections.tenant', []),
            [
                'driver'   => 'mysql',
                'host'     => $host ?? config('database.connections.mysql.host'),
                'port'     => $port ?? config('database.connections.mysql.port'),
                'database' => $database,
                'username' => $username,
                'password' => $password,
            ]
        ));

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function forget(): void
    {
        DB::setDefaultConnection(config('database.default'));
        DB::purge('tenant');
    }
}