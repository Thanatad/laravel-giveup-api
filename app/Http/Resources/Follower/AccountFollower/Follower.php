<?php

namespace App\Http\Resources\Follower\AccountFollower;

use App\Http\Resources\Account\AccountLess;
use Illuminate\Http\Resources\Json\JsonResource;

class Follower extends JsonResource
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
            'total' => $this->whenLoaded('followers')->count(),
            'follower' => AccountLess::collection($this->whenLoaded('followers')),

        ];
    }
}
