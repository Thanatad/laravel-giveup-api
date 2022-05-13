<?php

namespace App\Http\Resources\Story;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon;
class Account extends JsonResource
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
            'name' => $this->name,
            'image' => $this->image . '?v=' . Carbon::now()->format('YmdHisu'),
        ];
    }
}
