<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuruMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->peran === 'admin') {
            return $next($request);
        }
        
        // If they are administrator, they might try to access guru pages, we redirect them
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->peran === 'administrator') {
            return redirect()->route('administrator.dashboard');
        }

        return redirect()->route('login')->withErrors(['nama_pengguna' => 'Akses ditolak. Anda bukan Guru.']);
    }
}
