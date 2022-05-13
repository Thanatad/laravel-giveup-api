<?php

namespace App\Http\Resources\ChatConversations;

use App\Http\Resources\ChatMessages\Account;
use App\Http\Resources\ChatMessages\ChatMessages;
use App\Http\Resources\Post\PostLess;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversations extends JsonResource
{

    public function toArray($request)
    {
        $arrayData = [
            'id' => $this->id,
            'user_id_one' => [
                'id' => $this->user_id_one,
                'user_detail' => Account::collection(collect()->push($this->whenLoaded('account1')))[0],
                'message_last' => ChatMessages::collection($this->message_1_last),
            ],
            'messages' => ChatMessages::collection($this->whenLoaded('message')),
            'user_id_two' => [
                'id' => $this->user_id_two,
                'user_detail' => Account::collection(collect()->push($this->whenLoaded('account2')))[0],
                'message_last' => ChatMessages::collection($this->message_2_last),
            ],
            'name' => $this->name,
            'status' => $this->status,

            'type' => $this->type,
        ];

        if ($this->post_id != null) {
            $arrayData['post'] = PostLess::collection(collect()->push($this->whenLoaded('post')))[0];
        }

        if ($this->message_1_unseen) {
            $arrayData['user_id_one']['message_unseen'] = [
                'total' => $this->message_1_unseen->count(),
                'message' => ChatMessages::collection($this->message_1_unseen_limit)];
        }

        if ($this->message_2_unseen) {
            $arrayData['user_id_two']['message_unseen'] = [
                'total' => $this->message_2_unseen->count(),
                'message' => ChatMessages::collection($this->message_2_unseen_limit)];
        }

        return $arrayData;
    }
}
