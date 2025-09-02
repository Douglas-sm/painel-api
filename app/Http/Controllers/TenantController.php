<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * List all tenants.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tenants = Tenant::all();

        return response()->json([
            'tenants' => $tenants
        ], 200);
    }

    /**
     * Remove the specified tenant.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the tenant
            $tenant = Tenant::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Tenant not found'
            ], 404);
        }

        try {
            // Start transaction
            DB::beginTransaction();

            // Get the database name
            $databaseName = $tenant->database;

            // Drop the tenant's database
            DB::statement('DROP DATABASE IF EXISTS ' . $databaseName);

            // Delete the tenant record
            $tenant->delete();

            // Commit transaction
            DB::commit();

            // Return success response immediately after successful commit
            return response()->json([
                'message' => 'Tenant and its database deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            // Only rollback if transaction is active
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return response()->json([
                'message' => 'Error deleting tenant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new tenant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Start transaction if not already in one
            if (DB::transactionLevel() == 0) {
                DB::beginTransaction();
            }

            // Create tenant record
            $tenant = Tenant::create([
                'name' => $request->name,
                'domain' => $request->domain,
                'database' => 'painel_' . $request->domain,
            ]);

            // Create new database for the tenant
            DB::statement('CREATE DATABASE IF NOT EXISTS ' . $tenant->database);

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

            // Reset and reconfigure the tenant connection
            DB::purge('tenant');
            Config::set('database.default', 'tenant');

            // Run migrations with tenant path
            $options = [
                '--force' => true,
                '--path' => 'database/migrations/tenant',
            ];

            Artisan::call('migrate', $options);

            // Run specific migrations for adding default admin user and theme
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/tenant/2025_09_02_103100_add_default_admin_user.php'
            ]);

            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/tenant/2025_09_02_103200_add_default_theme_colors.php'
            ]);

            // Reset back to the central database connection
            Config::set('database.default', 'mysql');

            // Commit transaction if active
            if (DB::transactionLevel() > 0) {
                DB::commit();
            }

            // Return success response
            return response()->json([
                'message' => 'Tenant registered successfully',
                'tenant' => $tenant
            ], 201);

        } catch (\Exception $e) {
            // Only rollback if transaction is active
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return response()->json([
                'message' => 'Error registering tenant: ' . $e->getMessage()
            ], 500);
        }
    }
}
