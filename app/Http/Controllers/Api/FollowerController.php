<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Follower;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\PushNotification;
use App\Http\Resources\Follower\FollowerCollection;
use App\UserSetting;
use Auth;
use Illuminate\Http\Request;
use Validator;
use App\Http\Resources\Follower\AccountFollower\FollowerCollection as AccountFollowerCollection;

class FollowerController extends Controller
{
    protected $NotificationController;
    protected $pushNotification;

    public function __construct(NotificationController $NotificationController)
    {
        $this->NotificationController = $NotificationController;
        $this->pushNotification = new PushNotification();
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $following_user_id = $request->input('following_user_id');
        $follower_user_id = $request->input('follower_user_id');
        $is_approved = $request->input('is_approved');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $following_user_id || $follower_user_id || is_numeric($is_approved)) {
            $follower = Follower::when($id, function ($q) use ($id) {
                $q->where('id', $id);
            })
                ->when($following_user_id, function ($q) use ($following_user_id, $is_approved) {
                    $q->where('following_user_id', $following_user_id)
                        ->when(is_numeric($is_approved), function ($q) use ($is_approved) {
                            $q->where('is_approved', $is_approved);
                        });
                })
                ->when($follower_user_id, function ($q) use ($follower_user_id, $is_approved) {
                    $q->where('follower_user_id', $follower_user_id)
                        ->when(is_numeric($is_approved), function ($q) use ($is_approved) {
                            $q->where('is_approved', $is_approved);
                        });
                })
                ->when(is_numeric($is_approved), function ($q) use ($is_approved) {
                    $q->where('is_approved', $is_approved);
                })
                ->paginate($perPage);

            if (!$follower->isEmpty()) {
                return response(new FollowerCollection($follower));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $follower = Follower::paginate($perPage);

        return response(new FollowerCollection($follower));

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'following_user_id' => 'required|numeric|exists:accounts,user_id',
            'follower_user_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $setting = UserSetting::where('user_id', $request->following_user_id)->first();

        if (!$setting->is_follower_approve) {

            $follower = Follower::create([
                'following_user_id' => (int) $request->following_user_id,
                'follower_user_id' => (int) $request->follower_user_id,
                'is_approved' => 1,
            ]);

            $this->createNotification($request->follower_user_id, $request->following_user_id, 5);
        } else {

            $follower = Follower::create([
                'following_user_id' => (int) $request->following_user_id,
                'follower_user_id' => (int) $request->follower_user_id,
            ]);

            $this->createNotification($request->follower_user_id, $request->following_user_id, 4);
        }
        return response($follower, 200);

    }

    public function accountFollower($id)
    {
       $account = Account::with('followers')->where('user_id',$id)->paginate();
       return response(new AccountFollowerCollection($account));
    }

    public function followRequest(Request $request)
    {
        $follower_user_id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'following_user_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $setting = UserSetting::where('user_id', $request->following_user_id)->first();

        if (!$setting->is_follower_approve) {

            Account::find($follower_user_id)->followings()->attach(Account::find($request->following_user_id), ['is_approved' => 1]);

            $this->createNotification($follower_user_id, $request->following_user_id, 5);

        } else {

            Account::find($follower_user_id)->followings()->attach(Account::find($request->following_user_id));

            $this->createNotification($follower_user_id, $request->following_user_id, 4);
        }

        return response(['success' => 'Successfully followed.'], 200);
    }

    public function followDenied(Request $request)
    {
        $follower_user_id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'following_user_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        Account::find($follower_user_id)->followings()->detach(Account::find($request->following_user_id));

        return response(['success' => 'Successfully unfollowed.'], 200);
    }

    public function approve($id)
    {

        Follower::find($id)->update([
            'is_approved' => 1,
        ]);

        return response(['success' => 'Successfully approved'], 200);
    }

    private function createNotification(int $sender, int $recipient, int $code)
    {
        $request = new Request;

        $fcmToken = Account::select('fcm_token')->where('user_id', $recipient)->first()->fcm_token;
        $senderName = Account::select('name')->where('user_id', $sender)->first()->name;

        $message = $code == 5 ? 'เริ่มติดตามคุณ' : 'ได้ส่งคำขอติดตามคุณ';
        $this->pushNotification->message($recipient, $fcmToken, $senderName, $message, ['type' => 'FOLLOW', 'status' => 'done', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

        $request->replace([
            'sender_id' => $sender,
            'recipient_id' => $recipient,
            'code' => $code,
            'type' => 1,
        ]);

        $this->NotificationController->store($request);

    }

    public function destroy($id)
    {
        Follower::where('id', $id)->delete();

        return response(['success' => 'Successfully deleted'], 200);
    }
}
