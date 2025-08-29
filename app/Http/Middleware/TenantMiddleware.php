<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Allow-list central domains to bypass tenant switching
        // Example: CENTRAL_DOMAINS=localhost,principal.exemplo.com
        $centralDomains = array_filter(array_map('trim', explode(',', env('CENTRAL_DOMAINS', 'localhost'))));
        if (in_array($host, $centralDomains, true)) {
            // Keep using the central connection
            return $next($request);
        }

        // Extract subdomain (alfa from alfa.localhost or alfa.example.com)
        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? null;

        if (!$subdomain) {
            return $this->tenantNotFound($request);
        }

        $tenant = Tenant::where('domain', $subdomain)->first();
        if (!$tenant) {
            return $this->tenantNotFound($request);
        }

        // Configure tenant connection
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

        // Switch default connection cleanly
        DB::purge('tenant'); // ensure clean state if reused
        DB::setDefaultConnection('tenant');
        DB::reconnect('tenant');

        // Store current tenant for this request
        app()->forgetInstance('tenant');
        app()->instance('tenant', $tenant);

        return $next($request);
    }

    protected function tenantNotFound(Request $request)
    {
        // For API: return JSON 404; For web: redirect to central APP_URL
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }
        return redirect()->to(env('APP_URL'));
    }
}
