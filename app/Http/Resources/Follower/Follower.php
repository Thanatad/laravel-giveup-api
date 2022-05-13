<?php

namespace App\Http\Resources\Follower;

use Illuminate\Http\Resources\Json\JsonResource;

class Follower extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'following_user_id' => $this->following_user_id,
            'follower_user_id' => $this->follower_user_id,
            'is_approved' => $this->is_approved,
        ];
    }
}
