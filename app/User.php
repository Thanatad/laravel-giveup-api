<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'role', 'username', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function setting()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(socialAccount::class);
    }

    //change field email to username
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }


}
