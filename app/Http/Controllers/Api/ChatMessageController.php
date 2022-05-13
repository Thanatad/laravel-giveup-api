<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\ChatConversations;
use App\ChatMessage;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\ChatConversation as FireChatConversation;
use App\Http\FireControllers\ChatMessage as FireChatMessage;
use App\Http\FireControllers\PushNotification;
use App\Http\Resources\ChatMessages\ChatMessages as ChatMessagesResource;
use App\Http\Resources\ChatMessages\ChatMessagesCollection;
use Auth;
use Illuminate\Http\Request;
use Image;
use Validator;

class ChatMessageController extends Controller
{
    protected $fireChatMessage;
    protected $fireChatConversation;
    protected $pushNotification;

    public function __construct()
    {
        $this->fireChatMessage = new FireChatMessage();
        $this->fireChatConversation = new FireChatConversation();
        $this->pushNotification = new PushNotification();
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $ccId = $request->input('chat_conversation_id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $ccId) {
            $message = ChatMessage::with('account')
                ->when($id, function ($q) use ($id) {
                    $q->orWhere('id', $id);
                })
                ->when($ccId, function ($q) use ($ccId) {
                    $q->orWhere('chat_conversation_id', $ccId);
                })
                ->orderBy('id', 'desc')
                ->paginate($perPage);

            if (!$message->isEmpty()) {
                return response(new ChatMessagesCollection($message));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $message = ChatMessage::with('account')->orderBy('id', 'desc')->paginate($perPage);

        return response(new ChatMessagesCollection($message));
    }

    public function storeMessage(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'chat_conversation_id' => 'required|numeric|exists:chat_conversations,id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $userId = Auth::guard('api')->user()->id;

        $message = new ChatMessage;
        $message->chat_conversation_id = (int) $request->chat_conversation_id;
        $message->user_id = $userId;
        $message->save();

        if ($request->hasfile('message')) {
            $dirPath = public_path('/storage/conversation/' . $request->chat_conversation_id . '/');

            if (false !== mb_strpos($request->message->getMimeType(), "image")) {
                if (!file_exists($dirPath)) {
                    mkdir($dirPath, 0777, true);
                }

                if ($request->file('message')->isValid()) {

                    $extension = $request->file('message')->getClientOriginalExtension();
                    $image_name = rand(100, 999999) . time() . '.' . $extension;

                    $image = Image::make($request->file('message'));

                    $image->save($dirPath . $image_name);

                    $img_url = env('APP_URL') . '/storage/conversation/' . $request->chat_conversation_id . '/' . $image_name;
                    $message->update(['message' => $img_url, 'type' => 2]);
                }

            } else {
                if ($request->file('message')->isValid()) {
                    $filenameWithExt = $request->file('message')->isValid()->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $request->file('message')->isValid()->getClientOriginalExtension();
                    $fileNameToStore = $filename . rand(100, 999999) . time() . '.' . $extension;
                    $video_url = env('APP_URL') . '/storage/conversation/' . $request->chat_conversation_id . '/' . $fileNameToStore;
                    $request->file('message')->move($dirPath, $fileNameToStore);
                    $message->update(['message' => $video_url, 'type' => 3]);
                }
            }
        } else {
            $message->update(['message' => $request->message]);
        }

        $response = ChatMessage::with('account')->where('id', $message->id)->first();

        $this->fireChatMessage->create(json_decode((new ChatMessagesResource($response))->toJson(), true));

        $this->fireChatConversation->updateConversation($message->chat_conversation_id);

        $fcm = $this->getFcmToken($request->chat_conversation_id, $userId);

        $this->pushNotification->message($fcm['userId'], $fcm['fcmToken'], 'ได้รับข้อความใหม่', $response->account->name . ': ' . $request->message, ['type' => 'CHATMESSAGE', 'conversation_id' => $request->chat_conversation_id, 'status' => 'done', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

        return response(new ChatMessagesResource($response));
    }

    public function MessageSeenAll($id)
    {

        $message = ChatMessage::with('account')->where('chat_conversation_id', $id)
            ->where('user_id', '<>', Auth::user()->id)
            ->where('is_seen', 0)
            ->get();

        ChatMessage::where('chat_conversation_id', $id)
            ->where('user_id', '<>', Auth::user()->id)
            ->where('is_seen', 0)
            ->update(['is_seen' => 1]);

        foreach ($message as $item) {
            $item['is_seen'] = 1;
            $this->fireChatMessage->update(json_decode((new ChatMessagesResource($item))->toJson(), true), $id, $item->id);
        }

        $this->fireChatConversation->updateConversation($id);

        return response(['success' => 'Successfully seen message all'], 200);
    }

    public function MessageSeen($id)
    {
        $message = ChatMessage::find($id);
        $message->is_seen = 1;
        $message->save();

        $response = ChatMessage::with('account')->where('id', $message->id)->first();

        $this->fireChatMessage->update(json_decode((new ChatMessagesResource($response))->toJson(), true), $message->chat_conversation_id, $message->id);

        $this->fireChatConversation->updateConversation($message->chat_conversation_id);

        return response(['success' => 'Successfully seen message'], 200);
    }

    private function getFcmToken(int $conversationId, int $userId)
    {
        $targetUser = ChatConversations::select('user_id_one', 'user_id_two')->find($conversationId);

        if ($targetUser->user_id_one != $userId) {
            return ['userId' => $targetUser->user_id_one, 'fcmToken' => Account::select('fcm_token')->where('user_id', $targetUser->user_id_one)->first()->fcm_token];
        }

        if ($targetUser->user_id_two != $userId) {
            return ['userId' => $targetUser->user_id_two, 'fcmToken' => Account::select('fcm_token')->where('user_id', $targetUser->user_id_two)->first()->fcm_token];
        }
    }

}
