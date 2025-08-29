<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all users passwords to "Do741852!" across all tenant databases';

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

        $this->info('Starting password update for ' . $tenants->count() . ' tenants...');

        foreach ($tenants as $tenant) {
            $this->info("Updating passwords for tenant: {$tenant->name} (Database: {$tenant->database})");

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

            try {
                // Store current tenant for context
                app()->instance('tenant', $tenant);

                // Update all user passwords in this tenant database
                $count = User::count();

                if ($count > 0) {
                    $updated = User::query()->update([
                        'password' => Hash::make('Do741852!')
                    ]);

                    $this->info("Updated passwords for {$updated} users in tenant: {$tenant->name}");
                } else {
                    $this->info("No users found for tenant: {$tenant->name}");
                }

                $this->info("Password update completed for tenant: {$tenant->name}");
            } catch (\Exception $e) {
                $this->error("Error processing tenant {$tenant->name}: " . $e->getMessage());
            }
        }

        // Reset back to the central database connection
        Config::set('database.default', 'mysql');

        $this->info('All user passwords have been updated to "Do741852!"');
    }
}
