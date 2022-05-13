<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\PostComment\PostComment;
use App\Http\Resources\PostDonate\PostDonate;
use App\Http\Resources\Story\Account;
use Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class Post extends JsonResource
{

    public function toArray($request)
    {

        $arrayData = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_detail' => Account::collection(collect()->push($this->whenLoaded('account')))[0],
            'content' => $this->content ?? '',
            'file_type' => $this->file_type,
            'type' => $this->type,
            'location_name' => $this->location_name ?? '',
            'is_comment' => $this->is_comment,
            'file' => File::collection($this->whenLoaded('files')),
            'like' => ['is_like' => $this->liked($this->whenLoaded('likes')), 'numb' => $this->whenLoaded('likes')->count(), 'like' => Likes::collection($this->whenLoaded('likeLimit'))],
            'share' => $this->whenLoaded('shareLog')->count(),
            'comments' => ['totall' => $this->whenLoaded('comments')->count(), 'comment' => PostComment::collection($this->whenLoaded('commentParents'))],
            'created_at' => ['date_time' => $this->created_at->format('Y-m-d H:i:s'), 'ms' => strtotime($this->created_at->format('Y-m-d H:i:s')) * 1000],
            // 'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        if ($this->type == 2) {
            $arrayData['post_donate'] = PostDonate::collection(collect()->push($this->whenLoaded('postDonate')))[0];
            $arrayData['object_categories'] = $this->whenLoaded('objectCategories');
        }

        return $arrayData;

    }

    private function liked($likes)
    {
        $userId = Auth::user() ? Auth::user()->id : 0;
        foreach ($likes as $item) {
            if ($item['user_id'] == $userId) {
                return 1;
            }
        }
        return 0;
    }
}
