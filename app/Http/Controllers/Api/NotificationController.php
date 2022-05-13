<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\FireControllers\Notification as FireNotification;
use App\Http\Resources\Notification\Notification as NotificationResource;
use App\Http\Resources\Notification\NotificationCollection;
use App\Notification;
use Auth;
use Illuminate\Http\Request;
use Validator;

class NotificationController extends Controller
{

    protected $fireNotification;

    public function __construct()
    {
        $this->fireNotification = new FireNotification();
    }

    public function index(Request $request)
    {
        $id = $request->input('id');
        $recipient = $request->input('recipient_id');
        $sender = $request->input('sender_id');
        $type = $request->input('type');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $recipient || $type || $sender) {
            $noti = Notification::with('codes', 'post.files', 'sender', 'recipient')
                ->when($id || $recipient || $sender || $type, function ($q) use ($id, $recipient, $sender, $type) {
                    if ($type && ($recipient || $sender)) {
                        $q->when($sender, function ($q) use ($sender, $type) {
                            $q->Where('sender', $sender)->where('type', $type);
                        })->when($recipient, function ($q) use ($recipient, $type) {
                            $q->Where('recipient_id', $recipient)->where('type', $type);
                        });
                    } else if ($recipient) {
                        $q->where('recipient_id', $recipient);
                    } else if ($sender) {
                        $q->where('sender_id', $sender);
                    } else if ($type) {
                        $q->where('type', $type);
                    } else {
                        $q->where('id', $id);
                    }
                })
                ->where('status', '>=', 0)
                ->latest()
                ->paginate($perPage);

            if (!$noti->isEmpty()) {
                return response(new NotificationCollection($noti));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $noti = Notification::with('codes', 'post.files', 'sender', 'recipient')->where('status', '>=', 0)->latest()->paginate($perPage);

        return response(new NotificationCollection($noti));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|numeric|exists:accounts,user_id',
            'recipient_id' => 'required|numeric|exists:accounts,user_id',
            'post_id' => 'numeric|exists:posts,id',
            'code' => 'numeric',
            'status' => 'numeric',
            'type' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $noti = Notification::create([
            'sender_id' => $request->sender_id,
            'recipient_id' => $request->recipient_id,
            'post_id' => $request->post_id,
            'code' => $request->code,
            'status' => $request->status ?? 0,
            'type' => $request->type,
        ]);

        $response = Notification::with('codes', 'post.files', 'sender', 'recipient')->find($noti->id);

        $this->fireNotification->create(json_decode((new NotificationResource($response))->toJson(), true));

        return response($noti);
    }

    public function update(Request $request, $id)
    {
        $noti = Notification::find($id);

        $request->has('status') ? $noti->status = $request->status : '';
        $request->has('code') ? $noti->code = $request->code : '';
        $request->has('is_readed') ? $noti->is_readed = $request->is_readed : '';
        $noti->save();

        $response = Notification::with('codes', 'post.files', 'sender', 'recipient')->find($id);

        $this->fireNotification->update(json_decode((new NotificationResource($response))->toJson(), true), $id);

        return response($noti, 200);
    }

    public function notificationReaded($id)
    {
        Notification::where('id', $id)->update(['is_readed' => 1]);

        $response = Notification::with('codes', 'post.files', 'sender', 'recipient')->find($id);

        $this->fireNotification->update(json_decode((new NotificationResource($response))->toJson(), true), $id);

        return response(['success' => 'Successfully readed.'], 200);
    }

    public function notificationReadedAll($type)
    {
        $notification = Notification::with('codes', 'post.files', 'sender', 'recipient')->where('type', $type)
            ->where('recipient_id', Auth::user()->id)
            ->where('is_readed', 0)
            ->get();

        Notification::where('type', $type)
            ->where('recipient_id', Auth::user()->id)
            ->where('is_readed', 0)
            ->update(['is_readed' => 1]);

        foreach ($notification as $item) {
            $item['is_readed'] = 1;
            $this->fireNotification->update(json_decode((new NotificationResource($item))->toJson(), true), $item->id);
        }

        return response(['success' => 'Successfully readed all'], 200);
    }

    public function notificationId(int $postId, int $recipientId)
    {
        return Notification::where('post_id', $postId)
            ->where('recipient_id', $recipientId)
            ->where('type', 2)
            ->where('code', '<>', 16)
            ->orderBy('id', 'desc')->first();
    }
}
