<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    /**
     * Get theme colors by tenant ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTenantTheme($id)
    {
        // Ensure we're using the central database for tenant lookup
        Config::set('database.default', 'mysql');
        DB::purge('mysql');

        // Find the tenant by ID from the central database
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'The specified tenant does not exist'
            ], 404);
        }

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

        // Get theme data
        $theme = Theme::first();

        if (!$theme) {
            return response()->json([
                'error' => 'Theme not found',
                'message' => 'No theme data exists for this tenant'
            ], 404);
        }

        // Return theme colors only
        return response()->json([
            'primary' => $theme->primary,
            'secondary' => $theme->secondary
        ]);
    }
    /**
     * Get theme data for the current tenant
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTheme(Request $request)
    {
        // Ensure we're using the central database for tenant lookup
        Config::set('database.default', 'mysql');
        DB::purge('mysql');

        // Get tenant from the subdomain/prefix
        $prefix = $request->route('prefix');

        // Find the tenant by domain from the central database
        $tenant = Tenant::where('domain', $prefix)->first();

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'The specified tenant does not exist'
            ], 404);
        }

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

        // Get theme data
        $theme = Theme::first();

        if (!$theme) {
            return response()->json([
                'error' => 'Theme not found',
                'message' => 'No theme data exists for this tenant'
            ], 404);
        }

        // Return theme data
        return response()->json([
            'tenant' => $tenant->name,
            'theme' => [
                'primary' => $theme->primary,
                'secondary' => $theme->secondary
            ]
        ]);
    }

    /**
     * Update theme colors for a specific tenant
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTheme(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'primary' => 'required|string',
            'secondary' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation error',
                'message' => $validator->errors()
            ], 422);
        }

        // Ensure we're using the central database for tenant lookup
        Config::set('database.default', 'mysql');
        DB::purge('mysql');

        // Find the tenant by ID
        $tenant = Tenant::find($request->id);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'The specified tenant does not exist'
            ], 404);
        }

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

        // Find or create the theme
        $theme = Theme::first();
        if (!$theme) {
            $theme = new Theme();
        }

        // Update theme colors
        $theme->primary = $request->primary;
        $theme->secondary = $request->secondary;
        $theme->save();

        // Return updated theme data
        return response()->json([
            'message' => 'Theme colors updated successfully',
            'tenant' => $tenant->name,
            'theme' => [
                'id' => $theme->id,
                'primary' => $theme->primary,
                'secondary' => $theme->secondary
            ]
        ]);
    }
}
