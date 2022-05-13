<?php

namespace App\Http\Resources\Notification;

use App\Follower;
use App\Http\Resources\Account\AccountLess;
use App\Http\Resources\Post\PostLess;
use App\ThankPoint;
use Illuminate\Http\Resources\Json\JsonResource;

class Notification extends JsonResource
{

    public function toArray($request)
    {
        $arrayData = [
            'id' => $this->id,
            'sender' => AccountLess::collection(collect()->push($this->whenLoaded('sender')))[0],
            'recipient' => AccountLess::collection(collect()->push($this->whenLoaded('recipient')))[0],
            'code' => [
                'id' => $this->code,
                'name' => $this->replace($this->code, $this->whenLoaded('codes')->name),
            ],
            'status' => $this->status,
            'type' => $this->type,
            'is_readed' => $this->is_readed,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        if ($this->post_id != null) {
            $arrayData['post'] = PostLess::collection(collect()->push($this->whenLoaded('post')))[0];
        }

        if ($this->code == 4) {
            $follow = Follower::select('id')->where('following_user_id', $this->recipient_id)->where('follower_user_id', $this->sender_id)->first();
            $fId = $follow ? $follow->id : '';
            $arrayData['code']['follower_id'] = $fId;
        }

        return $arrayData;
    }

    private function replace(int $code, string $name)
    {
        switch ($code) {
            case 7:
                return str_replace('{name}', AccountLess::collection(collect()->push($this->whenLoaded('sender')))[0]->name, $name);
                break;
            case 13:
                return str_replace('{point}', ThankPoint::find(1)->point, $name);
                break;
            default:
                return $name;
                break;
        }
    }
}
