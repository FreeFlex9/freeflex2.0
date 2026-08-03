<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() instanceof Admin, 403, 'Acesso restrito a administradores.');

        return $next($request);
    }
}
