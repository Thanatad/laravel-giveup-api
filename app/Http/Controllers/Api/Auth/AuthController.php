<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\FireControllers\Notify as FireNotify;
use App\User;
use DB;
use Psr\Http\Message\ServerRequestInterface;
use \Laravel\Passport\Http\Controllers\AccessTokenController;

class AuthController extends AccessTokenController
{

    public function index(ServerRequestInterface $request)
    {
        $fireNotify = new FireNotify();

        $tokenResponse = parent::issueToken($request);
        $token = $tokenResponse->getContent();

        $tokenInfo = json_decode($token, true);

        $grantType = $request->getParsedBody()['grant_type'];

        if ($grantType == 'refresh_token') {

            $tokenParts = explode('.', $tokenInfo['access_token']);
            $tokenHeader = $tokenParts[1];

            $tokenHeaderJson = base64_decode($tokenHeader);

            $arrayTokenHeader = json_decode($tokenHeaderJson, true);

            $userToken = $arrayTokenHeader['jti'];

            $userId = DB::table('oauth_access_tokens')->where('id', $userToken)->value('user_id');

            $userUsername = User::select('username')->find($userId)['username'];

            $fireNotify->updateChat($userId);
            $fireNotify->updateNoti($userId);
        }

        $grantType == "social" ? $username = $request->getParsedBody()['email'] : ($grantType == 'refresh_token' ? $username = $userUsername : $username = $request->getParsedBody()['username']);

        $user = User::where('username', $username)->with('account', 'account.attentions')->first();
        $tokenInfo = collect($tokenInfo);
        $tokenInfo['user_id'] = $user->id;
        $tokenInfo['role'] = $user->role == 1 ? 'isUser' : 'isAdmin';

        if($user->account){
            $tokenInfo['check'] = ['is_first' => $user->is_first, 'is_bod' => $user->account->birth_date == null ? 0 : 1, 'is_gender' => $user->account->gender == null ? 0 : 1, 'is_attention' => !$user->account->attentions->isEmpty() ? 1 : 0];
            $fireNotify->updateChat($user->id);
            $fireNotify->updateNoti($user->id);
        }

        return $tokenInfo;
    }

}
