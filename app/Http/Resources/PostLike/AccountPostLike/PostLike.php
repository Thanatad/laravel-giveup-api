<?php

namespace App\Http\Resources\PostLike\AccountPostLike;

use Illuminate\Http\Resources\Json\JsonResource;

class PostLike extends JsonResource
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
                    $total += $item->likes->count();
                }
                return $total;
            }),
            'like' => $this->whenLoaded('posts', function () {
                $arrayData = [];
                foreach ($this->posts as $item) {
                    foreach ($item->likes as $value) {
                        $arrayData[] = $value;
                    }
                }
                return $arrayData;
            }),
        ];
    }
}
