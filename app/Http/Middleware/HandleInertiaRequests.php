<?php

namespace App\Http\Middleware;

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
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'locale']),
            ],
            'branding' => [
                'name'    => config('praxiquest.branding.name'),
                'tagline' => config('praxiquest.branding.tagline'),
                'logo'    => config('praxiquest.branding.logo'),
                'primary_color'   => config('praxiquest.branding.primary_color'),
                'secondary_color' => config('praxiquest.branding.secondary_color'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
