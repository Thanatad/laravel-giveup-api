<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Auth;
use Laravel\Passport\Client;

class GAuthController extends Controller
{
    use IssueTokenTrait;

    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
    }

    public function guest()
    {
        $user = User::find(1);
        $objToken = $user->createToken('TY');
        $strToken = $objToken->accessToken;

        $expiration = $objToken->token;

        return response()->json(["token_type" => "Bearer", "expires_in" => strtotime($expiration->expires_at) * 1000, "role" => "isGuest", "token" => $strToken], 200);
    }

    public function general(Request $request)
    {
        $credentials['username'] = $request->username;
        $credentials['password'] = $request->password;

        if (Auth::attempt($credentials)) return $this->issueToken($request, 'password');

        return response()->json(["error" => "invalid_credentials", "message" => "please retry later."], 401);
    }

}
