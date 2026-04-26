<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('api/*')) {
            return null;
        }

        if ($request->routeIs('admin.*') || $request->is('admin/*')) {
            return route('admin.login');
        }

        return '/';
    }
}
