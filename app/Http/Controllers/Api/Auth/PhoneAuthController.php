<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Otp;
use App\User;
use App\Follower;
use App\Http\Resources\Follower\FollowerCollection;
use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Sms;
use Validator;

class PhoneAuthController extends Controller
{

    use IssueTokenTrait;

    private $client;
    private $aMobileTest;

    public function __construct()
    {
        $this->client = Client::find(1);
        $this->aMobileFake = ['0811111111'];
    }

    public function otp($mobile)
    {
        $sms = new Sms();

        $sms->username = env('THSMS_USERNAME');
        $sms->password = env('THSMS_PASSWORD');

        $code = $sms->generateNumericOTP(4);

        $ref = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1) . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);

        $message = 'TY: OTP คือ ' . $code . ' (Ref.' . $ref . ')';

        $result = $sms->send('OTP', $mobile, $message);

        if ($result[0]) {

            $otp = new Otp;
            $otp->mobile = $mobile;
            $otp->otp = $code;
            $otp->ref = $ref;
            $otp->save();

            return response($otp);
        }

        return response()->json(["error" => "invalid", "message" => "Not recived otp."], 400);
    }

    public function otpSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|numeric|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 417);
        }

        $mobile = $request['mobile'];

        $this->chkFakeMobile($mobile) ? $sms = $this->fakeOtp($mobile) : $sms = $this->otp($mobile);

        return response()->json($sms->original);
    }

    public function phoneAuth(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:10',
            'otp' => 'required|numeric|min:4',
            'ref' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 417);
        }

        $user = User::where('username', $request->username)->first();

        if ($user) {
            return $this->login($request);
        } else {
            return $this->register($request);
        }
    }

    private function register(Request $request)
    {

        $getOtp = Otp::where('mobile', $request->username)->where('ref', $request->ref)->latest('created_at')->first();

        if ($getOtp && $request->otp == $getOtp->otp) {
            $dt = new DateTime();
            $date = $dt->format('Y-m-d');
            $otp_date = $getOtp->created_at->format('Y-m-d');

            if ($date == $otp_date) {

                $request['password'] = $request->otp;

                $user = User::create([
                    'username' => $request->username,
                    'password' => bcrypt($request->otp),
                ]);

                $user->account()->create([
                    'mobile' => $request->username,
                    'name' => 'TY' . sprintf('%06d', $user->id),
                    'image' => env('APP_URL') . '/storage/avatar/default-profile.png',
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

                return $this->issueToken($request, 'password');

            } else {
                return response()->json(["error" => "invalid_credentials", "message" => "The otp or ref were incorrect, please retry later."], 401);
            }
        } else {
            return response()->json(["error" => "invalid_credentials", "message" => "The otp or ref were incorrect, please retry later."], 401);
        }
    }

    private function login(Request $request)
    {

        $credentials = $request->all();

        $mobile = $credentials['username'];
        $ref = $credentials['ref'];

        $getOtp = Otp::where('mobile', $mobile)->where('ref', $ref)->latest('created_at')->first();

        if ($getOtp && $credentials['otp'] == $getOtp->otp) {
            $dt = new DateTime();
            $date = $dt->format('Y-m-d');
            $otp_date = $getOtp->created_at->format('Y-m-d');

            if ($date == $otp_date) {

                $credentials['username'] = $getOtp->mobile;
                $credentials['password'] = $getOtp->otp;
                unset($credentials['ref']);
                unset($credentials['otp']);

                $user = User::where('username', $getOtp->mobile)->first();
                $user->password = bcrypt($getOtp->otp);
                $user->is_first = false;
                $user->save();

            } else {
                return response()->json(["error" => "invalid_credentials", "message" => "The otp or ref were incorrect, please retry later."], 401);
            }
        } else {
            return response()->json(["error" => "invalid_credentials", "message" => "The otp or ref were incorrect, please retry later."], 401);
        }

        if (Auth::attempt($credentials)) {

            $request['username'] = $credentials['username'];
            $request['password'] = $credentials['password'];

            return $this->issueToken($request, 'password');
        }

        return response()->json(["error" => "invalid_credentials", "message" => "The user credentials were incorrect."], 401);

    }

    public function refresh(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required',
        ]);

        return $this->issueToken($request, 'refresh_token');
    }

    public function logout()
    {

        $accessToken = Auth::user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        $accessToken->revoke();

        return response()->json(['success' => 'Successfully logged out of application'], 200);

    }

    private function fakeOtp(string $mobile)
    {

        Otp::where('mobile', $mobile)->delete();

        $otp = Otp::create([
            'mobile' => $mobile,
            'otp' => '1234',
            'ref' => substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1) . substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4),
        ]);

        return response($otp);
    }

    private function chkFakeMobile(string $mobile)
    {
        return in_array($mobile, $this->aMobileFake);
    }

}
