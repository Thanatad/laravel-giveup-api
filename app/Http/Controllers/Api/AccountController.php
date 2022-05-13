<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\Account as AccountResource;
use App\Http\Resources\Account\AccountCollection;
use Auth;
use File;
use Illuminate\Http\Request;
use Image;
use Validator;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->input('id');
        $mobile = $request->input('mobile');
        $isAttention = $request->input('is_attention');
        $role = $request->input('role');
        $following = $request->input('following_user_id');
        $follower = $request->input('follower_user_id');
        $isStory = $request->input('is_story');
        $search = $request->input('search');
        $limit = $request->input('limit');
        $isRecommend = $request->input('is_recommend');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($isRecommend) {
            $follow = Account::find(Auth::user()->id)->followings()->pluck('user_id');
            $account = Account::with('user', 'followers', 'followings', 'posts.likes', 'hasFollow')->whereNotIn('user_id', $follow)->where('user_id', '!=', Auth::user()->id);

            $limit ? $res = $account->paginate($limit) : $res = $account->paginate($perPage);

            return $this->response($res);
        }

        if ($mobile) {
            $account = Account::with('user', 'followers', 'followings', 'posts.likes', 'hasFollow')->whereIn('mobile', array_map('intval', explode(',', $mobile)))
                ->paginate($perPage);

            return $this->response($account);
        }

        if (is_numeric($isStory)) {

            $account = $this->story($perPage);

            return $this->response($account);
        }

        if ($follower) {

            $account = $this->followings($perPage, $follower, $limit);

            return $this->response($account);
        }

        if ($following) {

            $account = $this->followers($perPage, $following, $limit);

            return $this->response($account);
        }

        if ($id && is_numeric($isAttention)) {
            $account = Account::with('user', 'followers', 'followings', 'attentions', )->where('user_id', $id)
                ->paginate($perPage);

            return $this->response($account);
        }

        if (is_numeric($isAttention)) {
            $account = Account::with('user', 'followers', 'followings', 'attentions', )->paginate($perPage);

            return $this->response($account);
        }

        if ($id || is_numeric($role)) {
            $account = Account::with('user', 'followers', 'followings', 'posts.likes', 'hasFollow')
                ->whereHas('user', function ($q) use ($role) {
                    $q->where('role', $role);
                })
                ->when($id, function ($q) use ($id) {
                    $q->orWhere('user_id', $id);
                })
                ->paginate($perPage);

            return $this->response($account);
        }

        if ($search) {
            $account = Account::with('user', 'followers', 'followings', 'hasFollow')
                ->orWhere('mobile', 'LIKE', '%' . $search . '%')
                ->orWhere('name', 'LIKE', '%' . $search . '%')
                ->orWhere('about', 'LIKE', '%' . $search . '%')
                ->orWhere('birth_date', 'LIKE', '%' . $search . '%')
                ->orWhere('gender', 'LIKE', '%' . $search . '%')
                ->orWhere('address', 'LIKE', '%' . $search . '%')
                ->orWhere('district', 'LIKE', '%' . $search . '%')
                ->orWhere('sub_district', 'LIKE', '%' . $search . '%')
                ->orWhere('province', 'LIKE', '%' . $search . '%')
                ->paginate($perPage);

            return response(new AccountCollection($account));
        }

        $account = Account::with('user', 'followers', 'followings', 'posts.likes', 'hasFollow')->paginate($perPage);

        return response(new AccountCollection($account));

    }

    public function update(Request $request, $id)
    {
        $account = Account::with('user', 'followers', 'followings')->where('user_id', $id)->first();

        $avatarUrl = ' ';

        if ($request->has('image') and $request->image != '') {

            $image_path = public_path('/storage/avatar/' . 'avatar-' . $id . '.png');
            if (file_exists($image_path)) {
                File::delete($image_path);
            }

            $request->file('image') ? $avatar = $request->file('image') : $avatar = $request->image;

            $name = 'avatar-' . $id;

            $image = Image::make($avatar);

            if ($image->width() > 300) {
                $image->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $image->save(public_path('/storage/avatar/' . $name . '.png'));

            $avatarUrl = env('APP_URL') . '/storage/avatar/' . $name . '.png';

            $account->image = ($avatarUrl == ' ' ? env('APP_URL') . '/storage/avatar/default-profile.png' : $avatarUrl);
        }

        $request->has('mobile') ? $account->mobile = $request->mobile : '';
        $request->has('name') ? $account->name = $request->name : '';
        $request->has('about') ? $account->about = $request->about : '';
        $request->has('about_more') ? $account->about_more = $request->about_more : '';
        $request->has('gender') ? $account->gender = $request->gender : '';
        $request->has('birth_date') ? $account->birth_date = $request->birth_date : '';
        $request->has('email') ? $account->email = $request->email : '';
        $request->has('address') ? $account->address = $request->address : '';
        $request->has('district') ? $account->district = $request->district : '';
        $request->has('sub_district') ? $account->sub_district = $request->sub_district : '';
        $request->has('province') ? $account->province = $request->province : '';
        $request->has('postcode') ? $account->postcode = $request->postcode : '';
        $request->has('point') ? $account->point = $request->point : '';
        $request->has('fcm_token') ? $account->fcm_token = $request->fcm_token : '';
        $request->has('role') ? User::where('id', $id)->update(['role' => $request->role]) : '';
        $account->update();

        return response(new AccountResource($account));

    }

    public function attentionCreate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'attention_id' => 'array',
            'attention_id.*' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        $account = Account::find($request->user_id)->attentions()->attach($request->attention_id);

        return response(AccountResource($account));
    }

    private function followings(int $perPage, int $following, $limit)
    {
        $follow = Account::find($following)->followings()->with('user', 'followings', 'followers', 'hasFollow');
        $limit ? $res = $follow->paginate($limit) : $res = $follow->paginate($perPage);
        return $res;
    }

    private function followers(int $perPage, int $follower, $limit)
    {
        $follow = Account::find($follower)->followers()->with('user', 'followings', 'followers', 'hasFollow');
        $limit ? $res = $follow->paginate($limit) : $res = $follow->paginate($perPage);
        return $res;
    }

    private function story(int $perPage)
    {
        $follow = Account::find(Auth::user()->id)->followings()->with('user', 'followings', 'followers', 'storys.user.account')->paginate($perPage);
        return $follow;
    }

    private function response($account)
    {
        if (!$account->isEmpty()) {
            return response(new AccountCollection($account));
        } else {
            return response(["error" => "not_found", "message" => 'Data not found.'], 404);
        }
    }
}
