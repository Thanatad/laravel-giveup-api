<?php

namespace App\Http\Resources\PostDonate;

use Illuminate\Http\Resources\Json\JsonResource;

class Account extends JsonResource
{

    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->name
        ];
    }
}
