<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! ($request->expectsJson() || collect($request->route()->middleware())->contains('api'))) {
            return route('login');
        }
    }
}
