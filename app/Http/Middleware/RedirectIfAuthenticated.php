<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): mixed
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->redirectTo($guard));
            }
        }

        return $next($request);
    }

    protected function redirectTo(string $guard): string
    {
        return match ($guard) {
            'admin'    => route('admin.dashboard'),
            'company'  => route('empresa.dashboard'),
            'provider' => route('prestador.dashboard'),
            default    => '/',
        };
    }
}
