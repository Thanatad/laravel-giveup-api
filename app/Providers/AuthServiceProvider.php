<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(GateContract $gate)
    {
        $this->registerPolicies();

        Passport::routes();

        $gate->define('isAdmin', function ($user) {
            return $user->role == '2';
        });
        $gate->define('isUser', function ($user) {
            return $user->role == '1';
        });

        $gate->define('isGuest', function ($user) {
            return $user->role == '0';
        });
        Passport::tokensExpireIn(now()->addDays(30));
        Passport::refreshTokensExpireIn(now()->addDays(60));

        Passport::personalAccessTokensExpireIn(now()->addYears(99));

    }
}
