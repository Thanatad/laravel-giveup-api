<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PostRecommend;
use Validator;
use App\Http\Resources\PostRecommend\PostRecommendCollection;
use App\Http\Resources\PostRecommend\PostRecommend as PostRecommendResource;

class PostRecommendController extends Controller
{
    public function index(Request $request)
    {
        $postId = $request->input('post_id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($postId) {
            $recommend = PostRecommend::where('post_id', $postId)
                ->paginate($perPage);

            if (!$recommend->isEmpty()) {
                return response(new PostRecommendCollection($recommend));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $recommend = PostRecommend::paginate($perPage);

        return response(new PostRecommendCollection($recommend));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|unique:post_recommends|numeric|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $recommend = PostRecommend::create([
            'post_id' => $request->post_id,
        ]);

        return response(new PostRecommendResource($recommend));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|unique:post_recommends|numeric|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $recommend = PostRecommend::where('post_id',$id)->update([
            'post_id' => $request->post_id
        ]);

        return response(['success' => 'Successfully updated'], 200);
    }

    public function destroy($id)
    {
        PostRecommend::where('post_id', $id)->delete();

        return response(['success' => 'Successfully deleted'], 200);
    }
}
