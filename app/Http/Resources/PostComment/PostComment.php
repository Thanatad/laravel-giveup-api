<?php

namespace App\Http\Resources\PostComment;

use Illuminate\Http\Resources\Json\JsonResource;

class PostComment extends JsonResource
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
            'post_id' => $this->post->id,
            'name' => $this->whenLoaded('user')->account->name,
            'comment' => $this->comment,
            'childrens' => PostCommentChildren::collection($this->whenLoaded('children')),
            'created_at' => ['date_time' => $this->created_at->format('Y-m-d H:i:s'), 'ms' => strtotime($this->created_at->format('Y-m-d H:i:s')) * 1000],
        ];
    }
}
