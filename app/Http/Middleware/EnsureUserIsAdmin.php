<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Hanya admin/owner yang boleh membuka halaman pengelolaan toko.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Halaman ini khusus admin toko.');
        }

        return $next($request);
    }
}
