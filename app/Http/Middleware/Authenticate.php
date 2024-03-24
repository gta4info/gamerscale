<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if(!Auth::user()) {
            $domain = parse_url(request()->root())['host'];
            $route = str_starts_with($domain, 'admin') ? 'admin.auth.discord' : 'auth.discord';

            return $request->expectsJson() ? null : route($route);
        }

        return null;
    }
}
