<?php

namespace App\Http\Resources\Story;

use Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Story extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_detail' => Account::collection(collect()->push($this->whenLoaded('user')->account))[0],
            'file_type' => $this->file_type,
            'file_path' => $this->file_path,
            'rotate' => $this->rotate ?? '',
            'expire_time' => ['is_expired' => $this->expired(strtotime($this->expire_time) * 1000), 'date_time' => $this->expire_time, 'ms' => strtotime($this->expire_time) * 1000],
            'is_seen' => $this->is_seen,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    private function expired($dateTime)
    {
        return $dateTime <= strtotime(Carbon::parse(date('Y-m-d H:i:s'))) * 1000 ? 1 : 0;
    }

}
