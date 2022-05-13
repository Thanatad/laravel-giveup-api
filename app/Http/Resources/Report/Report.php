<?php

namespace App\Http\Resources\Report;

use App\Http\Resources\Account\AccountLess;
use Illuminate\Http\Resources\Json\JsonResource;

class Report extends JsonResource
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
            'user' => AccountLess::collection(collect()->push($this->whenLoaded('account')))[0],
            'reporter' => AccountLess::collection(collect()->push($this->whenLoaded('reporter')))[0],
            'code' => ['id' => $this->whenLoaded('codes')->id, 'name' => $this->whenLoaded('codes')->name],
            'remark' => $this->remark ?? '',
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
