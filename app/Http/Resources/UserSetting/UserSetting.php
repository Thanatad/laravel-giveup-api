<?php

namespace App\Http\Resources\UserSetting;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Account\AccountLess;
class UserSetting extends JsonResource
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
            'user' => AccountLess::collection(collect()->push($this->whenLoaded('account')))[0],
            'is_push_notification'=>  $this->is_push_notification,
            'is_official'=> $this->is_official,
            'is_follower_approve'=>  $this->is_follower_approve
        ];
    }
}
