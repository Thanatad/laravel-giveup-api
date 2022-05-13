<?php

namespace App\Http\Resources\PostDonate;

use App\Http\Resources\PostDonate\PostDonateReason\PostDonateReason;
use App\Http\Resources\PostDonate\PostDonateReason\UserArray;
use Illuminate\Http\Resources\Json\JsonResource;
use Auth;

class PostDonate extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $arrayData = [
            'status' => $this->status,
            'timeout_in' => ['date_time' => $this->timeout_in, 'ms' => strtotime($this->timeout_in) * 1000],
            'delivery_method' => $this->delivery_method,
            'chosen_user' => $this->whenLoaded('account') == null ? "" : Account::collection(collect()->push($this->whenLoaded('account')))[0],
            'message' => $this->message ?? '',
            'reason' => [
                'is_reason' => $this->reasoned($this->whenLoaded('postDonateReasonLimit')),
                'total' => $this->whenLoaded('postDonateReason')->count(),
                'user' => UserArray::collection($this->whenLoaded('postDonateReason')),
                'reason' => PostDonateReason::collection($this->whenLoaded('postDonateReasonLimit'))],
        ];

        if ($this->delivery_method == 1) {
            $arrayData['address'] = $this->address;
            $arrayData['district'] = $this->district ?? '';
            $arrayData['sub_district'] = $this->sub_district ?? '';
            $arrayData['province'] = $this->province ?? '';
            $arrayData['postcode'] = $this->postcode ?? '';
        }

        return $arrayData;
    }

    public function reasoned($reasons){
        $userId = Auth::user()->id;
        foreach ($reasons as $item) {
             if($item['user_id'] == $userId) return 1;
        }
        return 0;
    }

}
