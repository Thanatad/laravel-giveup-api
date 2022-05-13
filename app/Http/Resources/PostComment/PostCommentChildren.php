<?php

namespace App\Http\Resources\PostComment;

use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentChildren extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->whenLoaded('user')->account->name,
            'comment' => $this->comment,
            'created_at' => ['date_time' => $this->created_at->format('Y-m-d H:i:s'), 'ms' => strtotime($this->created_at->format('Y-m-d H:i:s')) * 1000],
        ];
    }
}
