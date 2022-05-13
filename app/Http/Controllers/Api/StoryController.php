<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Resources\Story\Story as StoryResource;
use App\Http\Resources\Story\StoryCollection;
use App\Post;
use App\PostDonate;
use App\Story;
use App\StoryLog;
use App\UserSetting;
use Auth;
use Carbon;
use Illuminate\Http\Request;
use Image;
use Validator;

class StoryController extends Controller
{
    public function index(Request $request)
    {

        $id = $request->input('id');
        $userId = $request->input('user_id');
        $isFollowing = $request->input('is_following');
        $isFollowingReq = $request->input('is_following_req');
        $isExp = $request->input('is_exp');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($id || $userId || $isFollowing || $isFollowingReq || is_numeric($isExp)) {
            $story = Story::with('user.account')
                ->when($id, function ($q) use ($id) {
                    $q->where('id', $id);
                })
                ->when($userId, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->when($isFollowing || $isFollowingReq, function ($q) use ($isFollowing, $isFollowingReq, $isExp) {
                $listFollow = Account::find(Auth::user()->id)->followings()->with('user', 'followers')->pluck('user_id');
                $listPost = Post::where('type', 2)->where('user_id', Auth::user()->id)->pluck('id');
                $listFollowReq = PostDonate::whereIn('post_id', $listPost)->whereIn('chosen_user', $listFollow)->pluck('chosen_user');

                $listOfficialUser = UserSetting::where('is_official', 1)->pluck('user_id');

                $q->when($isFollowingReq, function ($q) use ($listFollowReq, $isExp) {
                    $q->where(function ($q) use ($listFollowReq) {
                        $q->whereIn('user_id', $listFollowReq);
                    })->where(function ($q) use ($isExp) {
                        $q->when(is_numeric($isExp), function ($q) use ($isExp) {
                            $q->when($isExp == 0, function ($q) {
                                $q->where('expire_time', '>', Carbon::now());
                            })->when($isExp == 1, function ($q) {
                                $q->where('expire_time', '<', Carbon::now());
                            });
                        });
                    });

                })->when($isFollowing, function ($q) use ($listFollow, $listOfficialUser, $isExp) {
                    $q->where(function ($q) use ($listFollow, $listOfficialUser) {
                        $q->whereIn('user_id', $listFollow)
                            ->orWhere('user_id', Auth::user()->id)
                            ->orWhere(function ($q) use ($listOfficialUser) {
                                $q->whereIn('user_id', $listOfficialUser);
                            });
                    })->where(function ($q) use ($isExp) {
                        $q->when(is_numeric($isExp), function ($q) use ($isExp) {
                            $q->when($isExp == 0, function ($q) {
                                $q->where('expire_time', '>', Carbon::now());
                            })->when($isExp == 1, function ($q) {
                                $q->where('expire_time', '<', Carbon::now());
                            });
                        });
                    });

                });
            })->when(is_numeric($isExp), function ($q) use ($isExp) {
                $q->when($isExp == 0, function ($q) {
                    $q->where('expire_time', '>', Carbon::now());
                })->when($isExp == 1, function ($q) {
                    $q->where('expire_time', '<', Carbon::now());
                });
            })
                ->paginate($perPage);

            if (!$story->isEmpty()) {
                return response(new StoryCollection($story));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }

        }

        $story = Story::with('user.account')->paginate($perPage);
        return response(new StoryCollection($story));
    }
    public function create()
    {
    }
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'file' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm,jpg,jpeg,png,bmp,tiff',
            'rotate' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $story = Story::create([
            'user_id' => (int) $request->user_id,
            'expire_time' => Carbon::now()->addHour(24),
            'rotate' => $request->rotate,
        ]);

        if ($request->hasfile('file')) {
            $dirPath = public_path('/storage/stories/' . $story->id . '/');

            if (false !== mb_strpos($request->file->getMimeType(), "image")) {
                if (!file_exists($dirPath)) {
                    mkdir($dirPath, 0777, true);
                }

                if ($request->file('file')->isValid()) {

                    $extension = $request->file('file')->getClientOriginalExtension();
                    $image_name = rand(100, 999999) . time() . '.' . $extension;

                    $image = Image::make($request->file('file'));

                    $image->save($dirPath . $image_name);

                    $img_url = env('APP_URL') . '/storage/stories/' . $story->id . '/' . $image_name;
                    $story->update(['file_path' => $img_url, 'file_type' => 1]);
                }

            } else {
                if ($request->file('file')->isValid()) {
                    $filenameWithExt = $request->file('file')->getClientOriginalName();
                    $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension = $request->file('file')->getClientOriginalExtension();
                    $fileNameToStore = $filename . rand(100, 999999) . time() . '.' . $extension;
                    $video_url = env('APP_URL') . '/storage/stories/' . $story->id . '/' . $fileNameToStore;
                    $request->file('file')->move($dirPath, $fileNameToStore);
                    $story->update(['file_path' => $video_url, 'file_type' => 2]);
                }
            }

        }

        return response(new StoryResource(Story::with('user.account')->find($story->id)));

    }
    public function show($id)
    {
    }
    public function edit($id)
    {
    }
    public function update(Request $request, $id)
    {
    }
    public function destroy($id)
    {
    }

    public function seen($id)
    {

        $validator = Validator::make(['story_id' => $id], [
            'story_id' => 'required|numeric|exists:stories,id',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        StoryLog::create([
            'story_id' => $id,
            'user_id' => Auth::user()->id,
        ]);

        return response(['success' => 'Successfully seen story'], 200);
    }
}
