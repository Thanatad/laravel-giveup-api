<?php

namespace App\Http\Resources\PostLike;

use Illuminate\Http\Resources\Json\JsonResource;

class PostLike extends JsonResource
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
            'post_id'=> $this->post_id,
            'user_id' => $this->user_id,
            'like' => $this->like
        ];
    }
}
