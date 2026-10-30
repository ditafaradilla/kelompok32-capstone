<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Hanya user dengan role 'admin' yang boleh lewat.
     * Belum login -> diarahkan ke login oleh middleware 'auth' (letakkan 'auth' sebelum middleware ini).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            abort(403, 'Halaman ini hanya dapat diakses oleh admin.');
        }

        return $next($request);
    }
}
