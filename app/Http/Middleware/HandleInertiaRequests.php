<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user'    => $request->user(),
                'admin'   => $request->user('admin')   ? ['id' => $request->user('admin')->id,   'email' => $request->user('admin')->email]   : null,
                'company'  => $request->user('company')  ? [
                    'id'          => $request->user('company')->id,
                    'trade_name'  => $request->user('company')->trade_name,
                    'status'      => $request->user('company')->status,
                    'approved_at' => $request->user('company')->approved_at?->toISOString(),
                ] : null,
                'provider' => $request->user('provider') ? [
                    'id'          => $request->user('provider')->id,
                    'name'        => $request->user('provider')->name,
                    'status'      => $request->user('provider')->status,
                    'approved_at' => $request->user('provider')->approved_at?->toISOString(),
                    'has_license' => (bool) $request->user('provider')->has_license,
                ] : null,
            ],
            'flash' => [
                'success'    => $request->session()->get('success'),
                'error'      => $request->session()->get('error'),
                'cnh_notice' => $request->session()->get('cnh_notice'),
            ],
        ];
    }
}
