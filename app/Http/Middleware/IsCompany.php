<?php

namespace App\Http\Middleware;

use App\Support\AccountBlockingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsCompany
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('company')->check()) {
            return redirect()->route('empresa.login');
        }

        $company = Auth::guard('company')->user();
        AccountBlockingService::liftIfExpired($company);

        if (!$company->active) {
            Auth::guard('company')->logout();
            return redirect()->route('empresa.login')->withErrors(['email' => AccountBlockingService::mensagemBloqueio($company)]);
        }

        return $next($request);
    }
}
