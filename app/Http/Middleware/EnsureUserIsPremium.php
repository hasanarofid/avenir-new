<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPremium
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasActivePremium() && !auth()->user()->hasRole('admin')) {
            return redirect()->route('langganan')->with('error', 'Akses Premium dibutuhkan untuk konten ini. Silakan berlangganan.');
        }

        return $next($request);
    }
}
