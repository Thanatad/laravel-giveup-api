<?php

namespace App\Http\Resources\PostDonate\PostDonateReason;

use Illuminate\Http\Resources\Json\JsonResource;

class PostDonateReason extends JsonResource
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
            'post_id' => $this->post_id,
            'name' => $this->whenLoaded('user')->account->name,
            'is_readed' => $this->is_readed,
            'reason' => $this->reason,
            'created_at' => ['date_time' => $this->created_at->format('Y-m-d H:i:s'), 'ms' => strtotime($this->created_at->format('Y-m-d H:i:s')) * 1000],
        ];
    }
}
