<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Image;

class FileUploadController extends Controller
{
    public function index(Request $request)
    {

        if ($request->hasfile('file')) {
            $dirPath = public_path('/storage/other/');

            if (false !== mb_strpos($request->file->getMimeType(), "image")) {

                if ($request->file('file')->isValid()) {

                    $extension = $request->file('file')->getClientOriginalExtension();
                    $image_name = rand(100, 999999) . time() . '.' . $extension;

                    $image = Image::make($request->file('file'));

                    $image->save($dirPath . $image_name);

                    $img_url = env('APP_URL') . '/storage/other/' . $image_name;

                    return response(["file_path" => $img_url], 200);
                }

            } else {
                return response(["error" => "invalid", "message" => 'File not image.'], 417);
            }
        } else {
            return response(["error" => "invalid", "message" => 'Data not file.'], 417);
        }
    }
}
