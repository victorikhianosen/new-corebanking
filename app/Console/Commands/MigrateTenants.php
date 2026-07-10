<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateTenants extends Command
{
    protected $signature = 'tenants:migrate
                            {--fresh : Drop & re-run all migrations per tenant}
                            {--seed}
                            {--tenant= : Limit to one code}';

    protected $description = 'Run the default migrations against every tenant database';

    public function handle(TenantManagerService $tenants): int
    {
        $query = Tenant::query()->where('status', 'active');

        if ($code = $this->option('tenant')) {
            $query->where('code', strtoupper($code));
        }

        foreach ($query->get() as $tenant) {
            $this->info("→ {$tenant->code} ({$tenant->database_name})");
            $tenants->switchTo($tenant);

            try {
                Artisan::call($this->option('fresh') ? 'migrate:fresh' : 'migrate', array_filter([
                    '--database' => 'tenant',
                    '--force'    => true,
                    '--seed'     => $this->option('seed') ?: null,
                ]));
                $this->line(trim(Artisan::output()));
            } catch (\Throwable $e) {
                $this->error("  Failed: {$e->getMessage()}");
            } finally {
                $tenants->forget();
            }
        }

        return self::SUCCESS;
    }
}