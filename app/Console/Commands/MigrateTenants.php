<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {--seed : Seed the database after migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenant databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all tenants from the central database
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found in the database.');
            return;
        }

        $this->info('Starting migrations for ' . $tenants->count() . ' tenants...');

        foreach ($tenants as $tenant) {
            $this->info("Migrating database for tenant: {$tenant->name} (Database: {$tenant->database})");

            // Configure connection for the tenant's database
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $tenant->database,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            // Reset and reconfigure the tenant connection to ensure a clean state
            DB::purge('tenant');
            Config::set('database.default', 'tenant');

            // Run migrations with tenant path
            $options = [
                '--force' => true,
                '--path' => 'database/migrations/tenant',
            ];

            try {
                $this->info("Running migrations for {$tenant->name}...");
                Artisan::call('migrate', $options);
                $this->info(Artisan::output());

                // If seed option provided, seed the database with tenant-specific data
                if ($this->option('seed')) {
                    // Store current tenant for seeders to use
                    app()->instance('tenant', $tenant);

                    $this->info("Seeding database for tenant: {$tenant->name}");
                    Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\TenantDatabaseSeeder',
                        '--force' => true
                    ]);
                    $this->info(Artisan::output());
                }

                $this->info("Operations completed for tenant: {$tenant->name}");
            } catch (\Exception $e) {
                $this->error("Error processing tenant {$tenant->name}: " . $e->getMessage());
            }
        }

        // Reset back to the central database connection
        Config::set('database.default', 'mysql');

        $this->info('All tenant migrations have been completed.');
    }
}
