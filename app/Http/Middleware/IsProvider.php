<?php

namespace App\Http\Middleware;

use App\Support\AccountBlockingService;
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
        AccountBlockingService::liftIfExpired($provider);

        if (!$provider->active) {
            Auth::guard('provider')->logout();
            return redirect()->route('prestador.login')->withErrors(['email' => AccountBlockingService::mensagemBloqueio($provider)]);
        }

        return $next($request);
    }
}
