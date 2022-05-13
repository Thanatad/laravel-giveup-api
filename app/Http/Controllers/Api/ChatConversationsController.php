<?php

namespace App\Http\Controllers\Api;

use App\ChatConversations;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\ChatConversation as FireChatConversation;
use App\Http\Resources\ChatConversations\Auth\ChatConversations as AuthChatConversationsResource;
use App\Http\Resources\ChatConversations\Auth\ChatConversationsCollection as AuthChatConversationsCollection;
use App\Http\Resources\ChatConversations\ChatConversationsCollection;
use Auth;
use Illuminate\Http\Request;
use Validator;

class ChatConversationsController extends Controller
{

    protected $fireChatConversation;

    public function __construct()
    {
        $this->fireChatConversation = new FireChatConversation();
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $userId = $request->input('user_id');
        $type = $request->input('type');
        $status = $request->input('status');

        $request->input('is_auth') ? $userId = Auth::user()->id : '';

        $perPage = 30;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $userId || $type || is_numeric($status)) {
            $conv = ChatConversations::with('messageLast', 'messageUnseen', 'messageUnseenLimit', 'account1', 'account2', 'post.files')
                ->when($type || $userId || $id || is_numeric($status), function ($q) use ($type, $userId, $id, $status) {
                    if (($userId && $type) || ($userId && $type && is_numeric($status))) {
                        $q->Where(function ($q) use ($type) {
                            $q->where('type', $type);
                        })->Where(function ($q) use ($userId) {
                            $q->orWhere('user_id_one', $userId)->orWhere('user_id_two', $userId);
                        })->when(is_numeric($status), function ($q) use ($status) {
                            $q->where('status', $status);
                        });
                    } else if ($type) {
                        $q->where('type', $type);
                    } else if (is_numeric($status)) {
                        $q->where('status', $status);
                    } else if ($id) {
                        $q->where('id', $id);
                    } else {
                        $q->orWhere('user_id_one', $userId)->orWhere('user_id_two', $userId);
                    }
                })
                ->paginate($perPage);

            if (!$conv->isEmpty()) {

                if ($request->input('is_auth')) {
                    return response(new AuthChatConversationsCollection($conv));
                }

                return response(new ChatConversationsCollection($conv));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $conv = ChatConversations::with('messageLast', 'messageUnseen', 'messageUnseenLimit', 'account1', 'account2', 'post.files')->paginate($perPage);

        return response(new ChatConversationsCollection($conv));
    }

    public function hasConversation(Request $request, $userId)
    {
        $authId = Auth::user()->id;
        $type = $request->has('type') ? $request->input('type') : 1;
        $postId = $request->input('post_id');

        $conv = ChatConversations::where(function ($q) use ($authId, $userId) {
            $q->Where(function ($q) use ($authId) {
                $q->orWhere('user_id_one', $authId)->orWhere('user_id_two', $authId);
            })->Where(function ($q) use ($userId) {
                $q->orWhere('user_id_one', $userId)->orWhere('user_id_two', $userId);
            });
        })
            ->when($postId, function ($q) use ($postId) {
                $q->where('post_id', $postId)->where('type', 2);
            })
            ->when(!$postId && $type, function ($q) use ($type) {
                $q->where('type', $type);
            })
        // ->where('status', 1)
            ->first();

        return response(['id' => $conv ? $conv->id : '', 'is_conversations' => $conv ? 1 : 0], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id_one' => 'required|numeric|exists:accounts,user_id',
            'user_id_two' => 'required|numeric|exists:accounts,user_id',
            'post_id' => 'numeric|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $conv = new ChatConversations;

        $request->has('post_id') ? $conv->type = 2 : '';

        $conv->user_id_one = $request->user_id_one;
        $conv->user_id_two = $request->user_id_two;
        $request->has('post_id') ? $conv->post_id = $request->post_id : '';
        $conv->save();
        $conv->name = 'conv_id_' . $conv->id;
        $conv->update();

        $this->fireChatConversation->updateConversation($conv->id);

        return response(new AuthChatConversationsResource(ChatConversations::find($conv->id)), 200);
    }

    public function fetchMessages(Request $request)
    {

        $conv = ChatConversations::with('message.account')->where('id', $request->input('chat_conversation_id'))->first();

        return response(new AuthChatConversationsResource($conv));
    }

    public function update(Request $request, $id)
    {
        $conv = ChatConversations::where('id', $id)->first();
        $request->has('name') ? $conv->name = $request->name : '';
        $request->has('status') ? $conv->status = (int) $request->status : '';
        $conv->update();

        $this->fireChatConversation->updateConversation($id);

        return response(['success' => 'Successfully updated'], 200);
    }

}
