<?php

namespace App\Http\Resources\ReportPost;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Account\AccountLess;
class ReportPost extends JsonResource
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
            'post_id' => $this->post_id,
            'reporter' => AccountLess::collection(collect()->push($this->whenLoaded('reporter')))[0],
            'code' => ['id' => $this->whenLoaded('codes')->id, 'name' => $this->whenLoaded('codes')->name],
            'remark' => $this->remark ?? '',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
