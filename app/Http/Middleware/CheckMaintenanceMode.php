<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceMap = [
            '/' => 'maint_home',
            'katalog*' => 'maint_katalog',
            'artikel*' => 'maint_artikel',
            'news*' => 'maint_news',
            'emiten*' => 'maint_emiten',
            'ki-brief*' => 'maint_ki_brief',
            'disclosure-radar*' => 'maint_disclosure',
            'tentang' => 'maint_tentang',
            'mitra*' => 'maint_mitra',
            'langganan*' => 'maint_langganan',
        ];

        // Ensure these critical paths are never blocked by maintenance mode logic
        $except = [
            'admin*',
            'login',
            'logout',
            'register',
            'migrate-setup',
            'api/*'
        ];

        foreach ($except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // Check against the maintenance map
        foreach ($maintenanceMap as $pattern => $settingKey) {
            // For root path exactly
            if ($pattern === '/' && $request->path() === '/') {
                if (Setting::getValue($settingKey) === '1') {
                    abort(503, 'Halaman Beranda Sedang Dalam Perawatan.');
                }
            } 
            // For other paths
            elseif ($pattern !== '/' && $request->is($pattern)) {
                if (Setting::getValue($settingKey) === '1') {
                    abort(503, 'Halaman Sedang Dalam Perawatan.');
                }
            }
        }

        return $next($request);
    }
}
