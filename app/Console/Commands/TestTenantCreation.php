<?php

namespace App\Console\Commands;

use App\Http\Controllers\TenantController;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestTenantCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-tenant-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant creation with default admin user and theme';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing tenant creation with default admin user and theme...');

        // Generate a random domain for testing
        $domain = 'test' . Str::random(8);
        $this->info("Using test domain: {$domain}");

        // Create a test request
        $request = Request::create('/api/tenants', 'POST', [
            'name' => 'Test Tenant',
            'domain' => $domain,
        ]);

        try {
            // Start a transaction for the test
            DB::beginTransaction();

            // Create controller instance and call register method
            $controller = new TenantController();
            $response = $controller->register($request);

            // Make sure transaction is properly handled
            if (DB::transactionLevel() > 0) {
                DB::commit();
            }
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->error('Error creating tenant: ' . $e->getMessage());
        }

        // Output response
        $this->info('Tenant created with response: ' . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT));

        // Now verify that the admin user and theme were created correctly
        try {
            // Switch to the tenant database
            $tenantDb = 'painel_' . $domain;
            Config::set('database.connections.test_tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $tenantDb,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            DB::purge('test_tenant');
            DB::reconnect('test_tenant');

            // Check for admin user
            $adminUser = DB::connection('test_tenant')->table('users')
                ->where('name', 'Admin')
                ->where('email', "admin@{$domain}.com")
                ->first();

            if ($adminUser) {
                $this->info('✅ Admin user created successfully');
                $this->info("Admin email: {$adminUser->email}");
            } else {
                $this->error('❌ Admin user not found');
            }

            // Check for theme
            $theme = DB::connection('test_tenant')->table('themes')
                ->where('primary', '#FF5733')
                ->where('secondary', '#33FF57')
                ->first();

            if ($theme) {
                $this->info('✅ Theme created successfully');
                $this->info("Theme colors: primary={$theme->primary}, secondary={$theme->secondary}");
            } else {
                $this->error('❌ Theme not found');
            }

            // Switch back to default connection
            DB::disconnect('test_tenant');

            // Cleanup - delete the test tenant
            $tenant = Tenant::where('domain', $domain)->first();
            if ($tenant) {
                // Drop the database
                DB::statement('DROP DATABASE IF EXISTS ' . $tenantDb);

                // Delete the tenant record
                $tenant->delete();

                $this->info('Test tenant and database cleaned up');
            }

        } catch (\Exception $e) {
            $this->error('Error during verification: ' . $e->getMessage());
        }
    }
}
