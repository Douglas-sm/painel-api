<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class ThemeController extends Controller
{
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
}
