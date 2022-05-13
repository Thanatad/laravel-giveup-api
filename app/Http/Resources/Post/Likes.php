<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\Account\AccountLess;
use Illuminate\Http\Resources\Json\JsonResource;

class Likes extends JsonResource
{

    public function toArray($request)
    {
        return AccountLess::collection(collect()->push($this->whenLoaded('account')))[0];
    }
}
