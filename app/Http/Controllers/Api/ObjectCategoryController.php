<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ObjectCategories\ObjectCategory as ObjectCategoryResource;
use App\Http\Resources\ObjectCategories\ObjectCategoryCollection;
use App\ObjectCategory;
use App\Post;
use App\PostDonateObjectCategory;
use Illuminate\Http\Request;

class ObjectCategoryController extends Controller
{
    public function index(Request $request)
    {

        $id = $request->input('id');
        $isMost = $request->input('is_most');
        $isSpecial = $request->input('is_special');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        // if(is_numeric($isMost)){
        //     $categories = ObjectCategory::withCount('posts')->latest('posts_count')->take(10)->with('posts.likes')->get();

        //     $categories->transform(function ($category) {
        //         $category->posts = Post::whereHas('objectCategories', function ($q) use ($category) {
        //             $q->where('post_id', $category->id);
        //         })
        //             ->get();
        //         return $category;
        //     });

        // }

        if ($id || is_numeric($isMost) || is_numeric($isSpecial)) {
            $objcategory = ObjectCategory::when($id, function ($q) use ($id) {
                $q->where('id', $id);
            })->when(is_numeric($isMost), function ($q) {
                $listPost = Post::withCount('likes')->where('type', 2)->having('likes_count', '>', 10)->latest('likes_count')->with('objectCategories')->pluck('id');
                $listCategory = array_unique(PostDonateObjectCategory::whereIn('post_id', $listPost)->pluck('objcategory_id')->toArray());
                $categories = ObjectCategory::pluck('id')->toArray();
                $ids = array_unique(array_merge($listCategory, $categories));

                $q->whereIn('id', $ids)->orderByRaw('FIELD (id, ' . implode(', ', $ids) . ') ASC');
            })->when(is_numeric($isSpecial), function ($q) {
                $q->orderBy('is_special', 'DESC');
            })
                ->paginate($perPage);

            if (!$objcategory->isEmpty()) {
                return response(new ObjectCategoryCollection($objcategory));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }

        }

        $objcategory = ObjectCategory::paginate($perPage);
        return response(new ObjectCategoryCollection($objcategory));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $objcategory = ObjectCategory::create([
            'name' => $request->name,
        ]);

        return response(new ObjectCategoryResource($objcategory));
    }

    public function update(Request $request, $id)
    {

        $objcategory = ObjectCategory::find($id);
        $request->has('name') ? $objcategory->name = $request->name : '';
        $request->has('is_special') ? $objcategory->is_special = $request->is_special : '';

        $objcategory->save();

        return response(['success' => 'Successfully updated'], 200);
    }

    public function destroy($id)
    {
        ObjectCategory::where('id', $id)->delete();

        return response(['success' => 'Successfully deleted'], 200);
    }
}
