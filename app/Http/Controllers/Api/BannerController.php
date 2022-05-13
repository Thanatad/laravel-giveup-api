<?php

namespace App\Http\Controllers\Api;

use App\Banner;
use App\Http\Controllers\Controller;
use App\Http\Resources\Banner\Banner as BannerResource;
use App\Http\Resources\Banner\BannerCollection;
use File;
use Illuminate\Http\Request;
use Image;
use Validator;

class BannerController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->input('id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id) {
            $banner = Banner::where('id', $id)
                ->paginate($perPage);

            if (!$banner->isEmpty()) {
                return response(new BannerCollection($banner));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }
        }

        $banner = Banner::paginate($perPage);

        return response(new BannerCollection($banner));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        if ($request->has('image')) {
            $banner = Banner::create();

            $image_path = public_path('/storage/banner/' . 'banner-' . $banner->id . '.png');

            $file = $request->file('image');

            $image = Image::make($file);

            $image->save($image_path);

            $imageUri = env('APP_URL') . '/storage/banner/' . 'banner-' . $banner->id . '.png';

            $banner->update([
                'file_path' => $imageUri,
            ]);
        }

        return response(new BannerResource($banner));

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }
        if ($request->has('image')) {
            $image_path = public_path('/storage/banner/' . 'banner-' . $id . '.png');
            if (file_exists($image_path)) {
                File::delete($image_path);
            }

            $image_path = public_path('/storage/banner/' . 'banner-' . $id . '.png');

            $file = $request->file('image');

            $image = Image::make($file);

            $image->save($image_path);

            $imageUri = env('APP_URL') . '/storage/banner/' . 'banner-' . $id . '.png';

            Banner::find($id)->update([
                'file_path' => $imageUri,
            ]);
        }

        return response(new BannerResource(Banner::find($id)));
    }

    public function destroy($id)
    {
        Banner::where('id', $id)->delete();

        $image_path = public_path('/storage/banner/' . 'banner-' . $id . '.png');
        if (file_exists($image_path)) {
            File::delete($image_path);
        }

        return response(['success' => 'Successfully delete'], 200);
    }
}
