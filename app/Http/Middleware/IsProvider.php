<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsProvider
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('provider')->check()) {
            return redirect()->route('prestador.login');
        }

        $provider = Auth::guard('provider')->user();

        if (!$provider->active) {
            Auth::guard('provider')->logout();
            return redirect()->route('prestador.login')->withErrors(['email' => 'Conta desativada.']);
        }

        return $next($request);
    }
}
