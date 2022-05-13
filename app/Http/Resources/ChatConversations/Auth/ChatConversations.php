<?php

namespace App\Http\Resources\ChatConversations\Auth;

use App\Http\Resources\ChatMessages\ChatMessages;
use App\Http\Resources\Post\PostLess;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversations extends JsonResource
{
    public function toArray($request)
    {
        $arrayData = [
            'id' => $this->id,
            'user_id_one' => $this->user_id_one,
            'user_id_two' => $this->user_id_two,
            'name' => $this->name,
            'status' => $this->status,
            'messages' => ChatMessages::collection($this->whenLoaded('message')),
            'message_last' => ChatMessages::collection($this->whenLoaded('messageLast')),
            'type' => $this->type,
        ];

        if ($this->post_id != null) {
            $arrayData['post'] = PostLess::collection(collect()->push($this->whenLoaded('post')))[0];
        }

        if ($this->relationLoaded('messageUnseen')) {
            $arrayData['message_unseen'] = [
                'total' => $this->whenLoaded('messageUnseen')->count(),
                'message' => ChatMessages::collection($this->whenLoaded('messageUnseenLimit'))];
        }

        return $arrayData;
    }
}
