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
            // Start transaction
            DB::beginTransaction();

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

            // Reset back to the central database connection
            Config::set('database.default', 'mysql');

            // Commit transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => 'Tenant registered successfully',
                'tenant' => $tenant
            ], 201);

        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            return response()->json([
                'message' => 'Error registering tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
