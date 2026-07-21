<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user() instanceof Company, 403, 'Acesso restrito a empresas.');

        return $next($request);
    }
}
