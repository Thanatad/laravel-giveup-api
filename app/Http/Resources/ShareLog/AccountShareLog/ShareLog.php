<?php

namespace App\Http\Resources\ShareLog\AccountShareLog;

use Illuminate\Http\Resources\Json\JsonResource;

class ShareLog extends JsonResource
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
            'total' => $this->whenLoaded('posts', function () {
                $total = 0;
                foreach ($this->posts as $item) {
                    $total += $item->shareLog->count();
                }
                return $total;
            }),
            'share' => $this->whenLoaded('posts', function () {
                $arrayData = [];
                foreach ($this->posts as $item) {
                    foreach ($item->shareLog as $value) {
                        $arrayData[] = $value;
                    }
                }
                return $arrayData;
            }),
        ];
    }
}
