<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdministratorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->peran === 'administrator') {
            return $next($request);
        }
        
        // If not administrator, maybe redirect to their respective dashboard or login
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->peran === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('login')->withErrors(['nama_pengguna' => 'Akses ditolak. Anda bukan Administrator.']);
    }
}
