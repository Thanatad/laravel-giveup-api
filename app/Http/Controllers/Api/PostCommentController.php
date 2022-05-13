<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\PushNotification;
use App\Http\Resources\PostComment\PostCommentCollection;
use App\Post;
use App\PostComment;
use Illuminate\Http\Request;
use Validator;

class PostCommentController extends Controller
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
            $comment = PostComment::with('children', 'user.account')
                ->when($id, function ($q) use ($id) {
                    $q->orWhere('id', $id);
                })
                ->when($post_id, function ($q) use ($post_id) {
                    $q->where('post_id', $post_id)->where('is_deleted', 0);
                })
                ->when($user_id, function ($q) use ($user_id) {
                    $q->where('user_id', $user_id)->where('is_deleted', 0);
                })
                ->where('parent_id', 0)

                ->paginate($perPage);

            if (!$comment->isEmpty()) {
                return response(new PostCommentCollection($comment));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $comment = PostComment::with('children', 'user.account')->where('parent_id', 0)->paginate($perPage);

        return response(new PostCommentCollection($comment));
    }
    public function create()
    {
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|numeric|exists:posts,id',
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'comment' => 'string',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $isPost = Post::where('id', $request->post_id)->where('user_id', $request->user_id)->first();

        $comment = PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => $request->user_id,
            'comment' => $request->comment,
            'parent_id' => 0,
        ]);

        if (!$isPost) $this->createNotification($request->user_id, Post::find($request->post_id)->user_id, $request->post_id, 1);

        return response($comment, 200);
    }

    public function replyStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|numeric|exists:posts,id',
            'comment_id' => 'required|numeric|exists:post_comments,id',
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'comment' => 'string',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $comment = PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => $request->user_id,
            'comment' => $request->comment,
            'parent_id' => $request->comment_id,
        ]);

        return response($comment, 200);
    }

    public function update(Request $request, $id)
    {
        PostComment::where('id', $id)->update([
            'is_deleted' => $request->is_deleted,
        ]);

        return response(['success' => 'Successfully updated'], 200);
    }

    public function destroy($id)
    {
    }

    private function createNotification(int $sender, int $recipient, int $post, int $code)
    {
        $request = new Request;

        $fcmToken = Account::select('fcm_token')->where('user_id', $recipient)->first()->fcm_token;
        $senderName = Account::select('name')->where('user_id', $sender)->first()->name;

        $this->pushNotification->message($recipient, $fcmToken, $senderName, 'ได้แสดงความคิดเห็นโพสของคุณ', ['type' => 'COMMENTPOST', 'post_id' => $post, 'status' => 'done', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

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
