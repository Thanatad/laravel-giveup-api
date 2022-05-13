<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\PushNotification;
use App\Http\Resources\PostLike\AccountPostLike\PostLikeCollection as AccountPostLikeCollection;
use App\Http\Resources\PostLike\PostLikeCollection;
use App\Post;
use App\PostLike;
use Illuminate\Http\Request;
use Validator;

class PostLikeController extends Controller
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
        $post_id = $request->input('post_id');
        $user_id = $request->input('user_id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($post_id || $user_id) {
            $like = PostLike::where('post_id', $post_id)
                ->paginate($perPage);

            if (!$like->isEmpty()) {
                return response(new PostLikeCollection($like));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $attention = PostLike::paginate($perPage);

        return response(new PostLikeCollection($attention));

    }
    public function create()
    {
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|numeric|exists:posts,id',
            'user_id' => 'required|numeric|exists:accounts,user_id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $wLike = PostLike::where('post_id', $request->post_id)->where('user_id', $request->user_id)->first();
        $isPost =  Post::where('id',$request->post_id)->where('user_id',$request->user_id)->first();

        if (!$wLike) {

            $like = PostLike::create([
                'post_id' => $request->post_id,
                'user_id' => $request->user_id,
                'like' => 1,
            ]);

            if(!$isPost) $this->createNotification($request->user_id, Post::find($like->post_id)->user_id, $like->post_id, 3);

            $msg = ['success' => 'Successfully liked.'];

        } else if ($wLike->like == 1) {
            PostLike::where('post_id', $request->post_id)->where('user_id', $request->user_id)->update([
                'like' => 0,
            ]);

            $msg = ['success' => 'Successfully unlike changed.'];
        } else {

            PostLike::where('post_id', $request->post_id)->where('user_id', $request->user_id)->update([
                'like' => 1,
            ]);

            $msg = ['success' => 'Successfully like changed.'];
        }

        return response($msg, 200);

    }

    public function update(Request $request, $id)
    {
    }
    public function destroy($id)
    {
    }

    public function accountLike($id)
    {
        $account = Account::with('posts.likes')->where('user_id', $id)->paginate();

        return response(new AccountPostLikeCollection($account));
    }

    private function createNotification(int $sender, int $recipient, int $post, int $code)
    {
        $request = new Request;

        $fcmToken = Account::select('fcm_token')->where('user_id', $recipient)->first()->fcm_token;
        $senderName = Account::select('name')->where('user_id', $sender)->first()->name;

        $this->pushNotification->message($recipient, $fcmToken, $senderName, 'ได้กดชื่นชอบโพสของคุณ', ['type' => 'LIKEPOST', 'post_id' => $post, 'status' => 'done', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

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
