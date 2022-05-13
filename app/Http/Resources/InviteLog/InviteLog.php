<?php

namespace App\Http\Resources\InviteLog;

use Illuminate\Http\Resources\Json\JsonResource;

class InviteLog extends JsonResource
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
            'mobile' => $this->mobile,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
