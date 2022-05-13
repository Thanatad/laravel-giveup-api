<?php

namespace App\Http\Controllers;

use App\Http\Resources\Post\Post as PostResource;
use App\Post;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $agent = new Agent();

        $postId = $request->input('post');
        $post = Post::with('account', 'objectCategories', 'files', 'likes', 'likeLimit.account', 'shareLog', 'comments')
            ->where('id', $postId)->first();

        if(!$post) abort(404, 'Page not found');

        $obj = new PostResource($post);

        $postRe = json_decode(($obj)->toJson(), true);

        $option = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        $data = [
            'id' => trim(json_encode($postRe['id'], $option), '"'),
            'userId' => trim(json_encode($postRe['user_id'], $option), '"'),
            'name' => trim(json_encode($postRe['user_detail']['name'], $option), '"'),
            'image' => trim(json_encode($postRe['user_detail']['image'], $option), '"'),
            'content' => trim(json_encode($postRe['content'], $option), '"'),
            'location' => trim(json_encode($postRe['location_name'], $option), '"'),
            'type' => trim(json_encode($postRe['type'], $option), '"'),
            'file' => trim(json_encode($postRe['file'][0]['file_path'], $option), '"'),
            'fileType' => trim(json_encode($postRe['file_type'], $option), '"'),
            'createAt' => $this->getTimeAgo($obj['created_at']),
            'like' => trim(json_encode($postRe['like']['numb'], $option), '"'),
            'likes' => $postRe['like']['like'],
            'comment' => trim(json_encode($postRe['comments']['totall'], $option), '"'),
            'share' => trim(json_encode($postRe['share'], $option), '"'),
            'platform' => $agent->platform(),
        ];

        $imgLike = [];

        for ($i = 0; $i < 4; $i++) {
            $imgLike[] = !empty($data['likes'][$i]) ? trim(json_encode($data['likes'][$i]['image'], $option), '"') : null;
        }

        return view("post", ['data' => $data, 'imgLikeI' => $imgLike[0], 'imgLikeII' => $imgLike[1], 'imgLikeIII' => $imgLike[2], 'imgLikeIV' => $imgLike[3]]);

    }

    public function getTimeAgo($carbonObject)
    {
        return str_ireplace(
            [' seconds ago', ' second ago', ' minutes ago', ' minute ago', ' hours ago', ' hour ago', ' days ago', ' day ago', ' weeks ago', ' week ago', 'month ago', 'months ago', 'year ago', 'years ago'],
            [' วินาทีที่แล้ว', ' วินาทีที่แล้ว', ' นาทีที่แล้ว', ' นาทีที่แล้ว', ' ชั่วโมงที่แล้ว', ' ชั่วโมงที่แล้ว', ' วันที่แล้ว', ' วันที่แล้ว', ' สัปดาห์ที่แล้ว', ' สัปดาห์ที่แล้ว', ' เดือนที่แล้ว', ' เดือนที่แล้ว', ' ปีที่แล้ว', ' ปีที่แล้ว'],
            $carbonObject->diffForHumans()
        );
    }
}
