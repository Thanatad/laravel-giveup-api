<?php

namespace App\Http\Resources\Account;

use App\Report;
use App\Http\Resources\Story\Story;
use Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Account extends JsonResource
{

    public function toArray($request)
    {
        $arrayData = [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'gender' => $this->gender ?? '',
            'about' => $this->about ?? '',
            'about_more' => $this->about_more ?? '',
            'birth_date' => $this->birth_date ?? '',
            'email' => $this->email ?? '',
            'role' => $this->whenLoaded('user')->role,
            'image' => $this->image . '?v=' . Carbon::now()->format('YmdHisu'),
            'mobile' => $this->mobile ?? '',
            'address' => $this->address ?? '',
            'district' => $this->district ?? '',
            'sub_district' => $this->sub_district ?? '',
            'province' => $this->province ?? '',
            'postcode' => $this->postcode ?? '',
            'like' => $this->whenLoaded('posts', function () {
                $total = 0;
                foreach ($this->posts as $item) {
                    $total += $item->likes->count();
                }
                return $total;
            }),

            'follower' => $this->whenLoaded('followers')->count(),
            'following' => $this->whenLoaded('followings')->count(),
            'point' => $this->point,
            'story' => Story::collection($this->whenLoaded('storys')),
            'fcm_token' => $this->fcm_token,
            'report_count' => $this->calCountReport(),
            'report_end_date' => $this->report_end_date ?? '',
            'attention' => $this->whenLoaded('attentions'),
        ];

        if ($this->relationLoaded('hasFollow')) {
            $arrayData['is_follow'] = $this->whenLoaded('hasFollow')->isEmpty() ? 0 : 1;
        }

        return $arrayData;
    }

    private function calCountReport()
    {
        $cReport = Report::where('user_id', $this->user_id)->where('status', 2)->count();
        return ($this->report_end_date == null ? 0 : (Carbon::parse(Carbon::now()->toDateString())->lte(Carbon::parse($this->report_end_date))) && ($cReport == 0)) ? 3 : $cReport;
    }
}
