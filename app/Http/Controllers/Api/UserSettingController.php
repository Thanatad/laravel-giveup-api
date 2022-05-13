<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSetting\UserSetting as UserSettingResource;
use App\Http\Resources\UserSetting\UserSettingCollection;
use App\UserSetting;
use Illuminate\Http\Request;

class UserSettingController extends Controller
{
    public function index(Request $request)
    {

        $user_id = $request->input('user_id');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($user_id) {
            $setting = UserSetting::with('account')->where('user_id', $user_id)
                ->paginate($perPage);

            if (!$setting->isEmpty()) {
                return response(new UserSettingCollection($setting));
            } else {
                return response(["error" => "not_found", "message" => 'Data not found.'], 404);
            }

        }

        $setting = UserSetting::with('account')->paginate($perPage);
        return response(new UserSettingCollection($setting));

    }
    public function create()
    {
    }
    public function store(Request $request)
    {
    }
    public function show($id)
    {
    }
    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
        $setting = UserSetting::where('user_id', $id)->first();
        $request->has('is_follower_approve') ? $setting->is_follower_approve = (int) $request->is_follower_approve : '';
        $request->has('is_push_notification') ? $setting->is_push_notification = (int) $request->is_push_notification : '';
        $request->has('is_official') ? $setting->is_official = (int) $request->is_official : '';
        $setting->save();

        return response(new UserSettingResource($setting));
    }

    public function usersUpdate(Request $request)
    {
        $isFollowerApprove = $request->is_follower_approve;
        $isPushNotification = $request->is_push_notification;
        $isOfficial = $request->is_official;

        UserSetting::when(is_numeric($isFollowerApprove), function ($q) use ($isFollowerApprove) {
            $q->where('is_follower_approve', '<>', $isFollowerApprove)->update([
                'is_follower_approve' => $isFollowerApprove,
            ]);
        })->when(is_numeric($isPushNotification), function ($q) use ($isPushNotification) {
            $q->where('is_push_notification', '<>', $isPushNotification)->update([
                'is_push_notification' => $isPushNotification,
            ]);
        })->when(is_numeric($isOfficial), function ($q) use ($isOfficial) {
            $q->where('is_official', '<>', $isOfficial)->update([
                'is_official' => $isOfficial,
            ]);
        });

        return response(['success' => 'Successfully updated'], 200);
    }

    public function destroy($id)
    {
    }
}
