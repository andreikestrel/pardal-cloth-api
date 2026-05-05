<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? array_merge($request->user()->only('id', 'name', 'email'), [
                        'roles' => $request->user()->getRoleNames(),
                    ])
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'settings' => fn () => cache()->remember('app_settings', 3600, function () {
                $setting = Setting::first();
                return $setting ? array_merge($setting->only(
                    'company_name',
                    'primary_color',
                    'secondary_color',
                    'accent_color',
                    'logo',
                ), ['blog_enabled' => (bool) $setting->blog_enabled]) : [];
            }),
        ];
    }
}
