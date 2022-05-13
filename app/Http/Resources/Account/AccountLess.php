<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon;
class AccountLess extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'image' => $this->image . '?v=' . Carbon::now()->format('YmdHisu'),
        ];
    }
}
