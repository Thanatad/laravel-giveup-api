<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\SocialAccount;
use App\User;
use App\Follower;
use App\Http\Resources\Follower\FollowerCollection;
use DB;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Rule;
use Validator;

class SocialAuthController extends Controller
{
    use IssueTokenTrait;

    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
    }

    public function socialAuth(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'email',
            'mobile' => 'string|min:10',
            'image' => 'string',
            'provider' => 'required|in:facebook,google,line,apple',
            'provider_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 417);
        }

        !$request->has('email') ? $request['email'] = $request->provider_user_id . '@app-giveup.com' : '';

        $socialAccount = SocialAccount::where('provider', $request->provider)->where('provider_user_id', $request->provider_user_id)->first();

        if ($socialAccount) {
            $socialAccount->user()->update([
                'is_first' => false,
            ]);

            if ($request->image) if ($this->uriExists($request->image)) $socialAccount->account()->update(['image' => $request->image]);

            return $this->issueToken($request, 'social');
        }

        $user = User::where('username', $request->email)
            ->whereNotNull("username")
            ->first();

        if ($user) {
            $this->addSocialAccountToUserAccount($request, $user, true);
        } else {
            try {
                $this->createUserAccount($request);
            } catch (\Exception $e) {
                return response()->json(["error" => "unprocessable_entity", "message" => 'An Error Occured, please retry later'], 422);
            }
        }

        return $this->issueToken($request, 'social');
    }

    private function addSocialAccountToUserAccount(Request $request, User $user, $is_account)
    {

        $this->validate($request, [
            'provider' => ['required', Rule::unique('social_accounts')->where(function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })],
            'provider_user_id' => 'required',
        ]);

        $user->socialAccounts()->create([
            'provider' => $request->provider,
            'provider_user_id' => $request->provider_user_id,
        ]);

        if (!$is_account) {
            $imgDefault = env('APP_URL') . '/storage/avatar/default-profile.png';

            $user->account()->create([
                'mobile' => $request->mobile,
                'name' => $request->provider == "apple" ? 'TY' . sprintf('%06d', $user->id) : $request->name,
                'image' => $this->uriExists($request->image) ? $request->image : env('APP_URL') . '/storage/avatar/default-profile.png',
                'email' => $request->email,
                'fcm_token' => '',
            ]);

            $user->setting()->create([
                'is_follower_approve' => 0,
            ]);

            Follower::create([
                'following_user_id' => (int) 61,
                'follower_user_id' => (int) $user->id,
                'is_approved' => 1,
            ]);
            Follower::create([
                'following_user_id' => (int) 62,
                'follower_user_id' => (int) $user->id,
                'is_approved' => 1,
            ]);
            Follower::create([
                'following_user_id' => (int) 63,
                'follower_user_id' => (int) $user->id,
                'is_approved' => 1,
            ]);
        }
    }

    private function createUserAccount(Request $request)
    {

        DB::transaction(function () use ($request) {

            $user = User::create([
                'username' => $request->email,
            ]);

            $this->addSocialAccountToUserAccount($request, $user, false);
        });
    }

    private function uriExists(string $uri)
    {
        $headers = get_headers($uri);
        return stripos($headers[0], "200 OK") ? true : false;
    }
}
