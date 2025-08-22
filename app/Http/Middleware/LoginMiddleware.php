<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('is_logged_in')) {
            if (Session::get('NAMA_PENGGUNA') == 'USTER_PRINT') {
                config(['session.lifetime' => 1440]);
            }
            return $next($request);
        } else {
            Session::invalidate();
            Session::regenerateToken();
            return redirect('login');
        }
    }
}
