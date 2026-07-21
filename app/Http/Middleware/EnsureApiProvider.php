<?php

namespace App\Http\Middleware;

use App\Models\Provider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() instanceof Provider, 403, 'Acesso restrito a prestadores.');

        return $next($request);
    }
}
