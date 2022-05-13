<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostDonate\PostDonateReason\PostDonateReasonCollection;
use App\Post;
use App\PostDonateReason;
use Illuminate\Http\Request;
use Validator;
use App\Http\FireControllers\PushNotification;
use App\Account;
class PostDonateReasonController extends Controller
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
        $post_id = $request->input('post_id');
        $user_id = $request->input('user_id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $post_id || $user_id) {
            $reason = PostDonateReason::with('user.account')
                ->when($id, function ($q) use ($id) {
                    $q->orWhere('id', $id);
                })
                ->when($post_id, function ($q) use ($post_id) {
                    $q->orWhere('post_id', $post_id);
                })
                ->when($user_id, function ($q) use ($user_id) {
                    $q->orWhere('user_id', $user_id);
                })
                ->paginate($perPage);

            if (!$reason->isEmpty()) {
                return response(new PostDonateReasonCollection($reason));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $reason = PostDonateReason::with('user.account')->paginate($perPage);

        return response(new PostDonateReasonCollection($reason));

    }
    public function create()
    {
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|numeric|exists:post_donates,post_id',
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'reason' => 'string',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $reason = PostDonateReason::create([
            'post_id' => $request->post_id,
            'user_id' => $request->user_id,
            'reason' => $request->reason,
        ]);

        $this->createNotification($request->user_id, Post::find($request->post_id)->user_id, $request->post_id, 2);

        return response($reason, 200);
    }
    public function show($id)
    {
    }
    public function edit($id)
    {
    }
    public function update(Request $request, $id)
    {
    }
    public function destroy($id)
    {
    }

    public function reasonReaded($id)
    {
        PostDonateReason::where('id', $id)->update(['is_readed' => 1]);

        return response(['success' => 'Successfully readed.'], 200);
    }

    private function createNotification(int $sender, int $recipient, int $post, int $code)
    {
        $request = new Request;

        $fcmToken = Account::select('fcm_token')->where('user_id', $recipient)->first()->fcm_token;
        $senderName = Account::select('name')->where('user_id', $sender)->first()->name;

        $this->pushNotification->message($recipient, $fcmToken, $senderName, 'ได้ส่งเหตุผลที่อยากได้บนโพสของคุณ', ['type' => 'REASONPOST', 'post_id' => $post, 'status' => 'done', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

        $request->replace([
            'sender_id' => $sender,
            'recipient_id' => $recipient,
            'post_id' => $post,
            'code' => $code,
            'type' => 1,
        ]);

        $this->NotificationController->store($request);
    }
}
