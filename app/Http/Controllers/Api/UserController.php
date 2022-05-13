<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\Account as AccountResource;
use Auth;

class UserController extends Controller
{

    public function information()
    {
        $user = Auth::user();

        if ($user->role == 0) {
            return response(['role' => 'isGuest'], 200);
        }

        if ($user->role == 2) {
            return response(['role' => 'isAdmin'], 200);
        }

        $account = Account::with('user', 'followers', 'followings')->where('user_id', Auth::guard('api')->user()->id)->first();
        return response(new AccountResource($account));
    }

}
