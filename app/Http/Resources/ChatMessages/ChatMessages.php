<?php

namespace App\Http\Resources\ChatMessages;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessages extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'chat_conversation_id'=> $this->chat_conversation_id,
            'is_seen'=> $this->is_seen,
            'type'=> $this->type,
            'message'=> $this->message,
            'user_id'=> $this->user_id,
            'user_detail'=> Account::collection(collect()->push($this->whenLoaded('account')))[0],
            'created_at' => ['date_time' => $this->created_at->format('Y-m-d H:i:s'), 'ms' => strtotime($this->created_at->format('Y-m-d H:i:s')) * 1000],
        ];
    }
}
