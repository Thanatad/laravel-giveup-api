<?php

namespace App\Http\Resources\PostDonate\PostDonateReason;

use Illuminate\Http\Resources\Json\JsonResource;

class UserArray extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return  $this->user_id;
    }
}
