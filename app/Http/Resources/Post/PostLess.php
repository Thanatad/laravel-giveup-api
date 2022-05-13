<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;

class PostLess extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'file_type' => $this->file_type,
            'file' => File::collection($this->whenLoaded('files')),
        ];
    }
}
