<?php

namespace App\Http\Controllers\Api;

use App\Account;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\PushNotification;
use App\Http\Resources\Post\PostCollection;
use App\NotificationCode;
use App\Post;
use App\PostDonate;
use App\PostRecommend;
use App\Rules\File;
use App\ThankPoint;
use App\UserSetting;
use Auth;
use Carbon;
use Illuminate\Http\Request;
use Image;
use Validator;

class PostController extends Controller
{

    protected $NotificationController;
    protected $ReportController;
    private $pushNotification;
    private $request;

    public function __construct(NotificationController $NotificationController, ReportController $ReportController)
    {
        $this->NotificationController = $NotificationController;
        $this->ReportController = $ReportController;
        $this->pushNotification = new PushNotification();
        $this->request = new Request;
    }

    public function index(Request $request)
    {

        $id = $request->input('id');
        $location = $request->input('location');
        $type = $request->input('type');
        $userId = $request->input('user_id');
        $objCatagory = $request->input('catagory');
        $search = $request->input('search');
        $name = $request->input('name');
        $content = $request->input('content');
        $isFollowing = $request->input('is_following');
        $isFollowingReq = $request->input('is_following_req');
        $isMost = $request->input('is_most');
        $isRecommend = $request->input('is_recommend');

        $perPage = 50;
        if ($request->input('get') == 'all') {
            $perPage = 99999999999999;
        }

        if ($search || $id || $location || $type || $userId || $objCatagory || $name || $content || $isFollowing || $isFollowingReq || is_numeric($isMost) || is_numeric($isRecommend)) {
            $post = Post::with('account', 'objectCategories', 'files', 'likes', 'likeLimit.account', 'shareLog', 'postDonate', 'postDonate.account', 'postDonate.postDonateReason', 'postDonate.postDonateReasonLimit', 'postDonate.postDonateReasonLimit.user.account', 'objectCategories', 'commentParents.children', 'commentParents.user.account', 'comments')
                ->when($search, function ($q) use ($search, $type) {
                    $q->whereHas('account', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%')->orWhere('content', 'LIKE', '%' . $search . '%');
                    })->when($type, function ($q) use ($type) {
                        $q->where('type', $type);
                    });
                })->when($content, function ($q) use ($content) {
                $q->where('content', 'LIKE', '%' . $content . '%');
            })->when($name, function ($q) use ($name) {
                $q->whereHas('account', function ($q) use ($name) {
                    $q->where('name', 'LIKE', '%' . $name . '%');
                });
            })->when($objCatagory, function ($q) use ($objCatagory) {
                $q->whereHas('objectCategories', function ($q) use ($objCatagory) {
                    $q->whereIn('objcategory_id', array_map('intval', explode(',', $objCatagory)));
                });
            })->when($isFollowing || $isFollowingReq, function ($q) use ($isFollowing, $isFollowingReq) {
                $listFollow = Account::find(Auth::user()->id)->followings()->with('user', 'followers')->pluck('user_id');
                $listPost = Post::where('type', 2)->where('user_id', Auth::user()->id)->pluck('id');
                $listFollowReq = PostDonate::whereIn('post_id', $listPost)->whereIn('chosen_user', $listFollow)->pluck('chosen_user');

                $listOfficialUser = UserSetting::where('is_official', 1)->pluck('user_id');

                $q->when($isFollowingReq, function ($q) use ($listFollowReq) {
                    $q->whereIn('user_id', $listFollowReq)->where('is_deleted', 0);
                })->when($isFollowing, function ($q) use ($listFollow, $listOfficialUser) {
                    $q->where(function ($q) {
                        $q->where('is_deleted', 0);
                    })->where(function ($q) use ($listFollow, $listOfficialUser) {
                        $q->whereIn('user_id', $listFollow)
                            ->orWhere('user_id', Auth::user()->id)
                            ->orWhere(function ($q) use ($listOfficialUser) {
                                $q->whereIn('user_id', $listOfficialUser);
                            });
                    });
                });
            })->when($userId, function ($q) use ($userId, $type) {
                $q->where('user_id', $userId)
                    ->when($type, function ($q) use ($type) {
                        $q->where('type', $type);
                    });
            })->when($id, function ($q) use ($id) {
                $q->where('id', $id);
            })->when($location, function ($q) use ($location) {
                $q->where('location_name', 'LIKE', '%' . $location . '%');
            })->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })->when(is_numeric($isMost), function ($q) {
                $q->withCount('likes')->having('likes_count', '>', 10)->latest('likes_count');
            })->when(is_numeric($isRecommend), function ($q) {
                $q->whereIn('id', PostRecommend::pluck('post_id'));
            })
                ->where('is_deleted', 0)
                ->latest()
                ->paginate($perPage);

            return $this->response($post);
        }

        $post = Post::where('is_deleted', 0)->with('account', 'files', 'likes', 'likeLimit.account', 'shareLog', 'postDonate', 'postDonate.account', 'postDonate.postDonateReason', 'postDonate.postDonateReasonLimit', 'postDonate.postDonateReasonLimit.user.account', 'objectCategories', 'commentParents.children', 'commentParents.user.account', 'comments')->latest()->paginate($perPage);
        return $this->response($post);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:accounts,user_id',
            'type' => 'numeric',
            'location_name' => 'string',
            'content' => 'string',
            'is_comment' => 'numeric',
            'file' => [new File],
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 417);
        }

        //donate
        if ($request->type == 2) {

            $validator = Validator::make($request->all(), [
                'timeout_in' => ['required', 'regex:/^(0[0-9]|1[0-9]|2[0-4]):[0-5][0-9]$/'],
                'delivery_method' => 'required|numeric',
                'objcategory_id' => 'array',
                'objcategory_id.*' => 'numeric',
            ]);

            if ($validator->fails()) {
                return response($validator->errors(), 417);
            }

            if ($request->delivery_method == 1) {

                $validator = Validator::make($request->all(), [
                    'address' => 'required|string',
                    'district' => 'required|string',
                    'sub_district' => 'required|string',
                    'province' => 'required|string',
                    'postcode' => 'required',
                ]);

                if ($validator->fails()) {
                    return response($validator->errors(), 417);
                }

                $post = $this->postCreate($request);

                $timeout = $this->timeCalculator($request->timeout_in);

                PostDonate::create([
                    'post_id' => $post->id,
                    'delivery_method' => 1,
                    'timeout_in' => $timeout,
                    'address' => $request->address,
                    'district' => $request->district,
                    'sub_district' => $request->sub_district,
                    'province' => $request->province,
                    'postcode' => $request->postcode,
                ]);

                if ($request->has('objcategory_id')) {
                    Post::find($post->id)->objectCategories()->attach($request->objcategory_id);
                }

            } else {

                $post = $this->postCreate($request);

                $timeout = $this->timeCalculator($request->timeout_in);

                PostDonate::create([
                    'post_id' => $post->id,
                    'delivery_method' => 2,
                    'timeout_in' => $timeout,
                ]);

                if ($request->has('objcategory_id')) {
                    Post::find($post->id)->objectCategories()->attach($request->objcategory_id);
                }

            }

        } else {
            $post = $this->postCreate($request);
        }

        return response($post, 200);

    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make(['id' => $id], [
            'id' => 'numeric|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $post = Post::find($id);

        $request->has('content') ? $post->content = $request->content : '';
        $request->has('is_comment') ? $post->is_comment = $request->is_comment : '';
        $request->has('is_deleted') ? $post->is_deleted = $request->is_deleted : '';
        $post->update();

        return response($post);
    }

    public function donateUpdate(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric|exists:post_donates,post_id',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors()], 417);
        }

        $postDonate = PostDonate::where('post_id', $id)->first();
        $post = Post::find($id);

        if ($request->has('content') || $request->has('is_comment')) {
            $request->has('content') ? $post->content = $request->content : '';
            $request->has('is_comment') ? $post->is_comment = $request->is_comment : '';
            $post->update();
        }

        if ($request->has('status')) {
            $code = NotificationCode::get();

            switch ($request->status) {
                case 1:
                    $this->postStatusUpdate(1, $id);

                    $sender = $this->accountInfo($post->user_id);

                    $this->notificationUpdate(8, $id, 2, $sender['userId']);

                    $this->pushNotification->message($sender['userId'], $sender['fcmToken'], 'แจ้งเตือนใหม่', $code[7]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 1, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    break;
                case 2:
                    $postDonate->update([
                        'chosen_user' => $request->chosen_user,
                        'message' => $request->message,
                    ]);

                    $this->postStatusUpdate(2, $id);

                    $receiver = $this->accountInfo($request->chosen_user);
                    $sender = $this->accountInfo($post->user_id);

                    $this->pushNotification->message($sender['userId'], $sender['fcmToken'], 'แจ้งเตือนใหม่', $code[5]->name, ['type' => 'DONATEPOST', 'post_id' => $post, 'status' => 1, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    $this->pushNotification->message($receiver['userId'], $receiver['fcmToken'], 'แจ้งเตือนใหม่', str_replace('{name}', $sender['name'], $code[6]->name), ['type' => 'DONATEPOST', 'post_id' => $post, 'status' => 1, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

                    $this->notificationCreate($receiver['userId'], $sender['userId'], $id, 6, 1);
                    $this->notificationCreate($sender['userId'], $receiver['userId'], $id, 7, 1);
                    break;
                case 3:
                    $this->postStatusUpdate(3, $id);

                    $receiver = $this->accountInfo($postDonate->chosen_user);
                    $sender = $this->accountInfo($post->user_id);

                    $this->notificationUpdate(9, $id, 2, $sender['userId']);
                    $this->notificationUpdate(10, $id, 2, $receiver['userId']);

                    $this->pushNotification->message($sender['userId'], $sender['fcmToken'], 'แจ้งเตือนใหม่', $code[8]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 2, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    $this->pushNotification->message($receiver['userId'], $receiver['fcmToken'], 'แจ้งเตือนใหม่', $code[9]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 2, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    break;
                case 4:
                    $this->postStatusUpdate(4, $id);

                    $receiver = $this->accountInfo($postDonate->chosen_user);
                    $sender = $this->accountInfo($post->user_id);

                    $this->notificationUpdate(11, $id, 3, $sender['userId']);
                    $this->notificationUpdate(12, $id, 3, $receiver['userId']);

                    $this->pushNotification->message($sender['userId'], $sender['fcmToken'], 'แจ้งเตือนใหม่', $code[10]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 3, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    $this->pushNotification->message($receiver['userId'], $receiver['fcmToken'], 'แจ้งเตือนใหม่', $code[11]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 3, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    break;
                case 5:
                    $this->postStatusUpdate(5, $id);

                    $receiver = $this->accountInfo($postDonate->chosen_user);
                    $sender = $this->accountInfo($post->user_id);

                    $point = ThankPoint::find(1)->point;

                    $this->notificationUpdate(13, $id, 4, $sender['userId']);
                    $this->notificationUpdate(14, $id, 4, $receiver['userId']);

                    $this->pushNotification->message($sender['userId'], $sender['fcmToken'], 'แจ้งเตือนใหม่', str_replace('{point}', $point, $code[12]->name), ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 4, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    $this->pushNotification->message($receiver['userId'], $receiver['fcmToken'], 'แจ้งเตือนใหม่', $code[13]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 4, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

                    $this->upPoint($post->user_id, $point);
                    break;
                case 6:
                    $this->postStatusUpdate(6, $id);

                    $sender = $this->accountInfo($post->user_id);

                    $this->notificationUpdate(15, $id, 4, $sender['userId']);

                    $this->accountReport($post->user_id, $postDonate->chosen_user);

                    $this->pushNotification->message($sender['userId'], $sender['fcmToken'], 'แจ้งเตือนใหม่', $code[14]->name, ['type' => 'DONATEPOST', 'post_id' => $id, 'status' => 4, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);
                    break;
                default:
                    $this->postStatusUpdate($request->status, $post->user_id);
                    break;
            }
        }

        return response(['success' => 'Successfully updated'], 200);
    }

    private function checkTypeFile($file)
    {
        //1:none,2:img,3:video,4:imageURI

        if ($file) {

            if (is_array($file)) {
                if (is_string($file[0])) {
                    return 4;
                } else if (false !== mb_strpos($file[0]->getMimeType(), "image")) {
                    return 2;
                } else if (false !== mb_strpos($file[0]->getMimeType(), "video")) {
                    return 3;
                }

            } else {
                if (is_string($file)) {
                    return 4;
                } else if (false !== mb_strpos($file->getMimeType(), "image")) {
                    return 2;
                } else if (false !== mb_strpos($file->getMimeType(), "video")) {
                    return 3;
                }
            }

        } else {
            return 1;
        }
    }

    private function postCreate(Request $request)
    {

        $fileType = $this->checkTypeFile($request->file);

        $post = Post::create([
            'user_id' => (int) $request->user_id,
            'type' => $request->has('type') ? $request->type : 1,
            'is_comment' => $request->has('is_comment') ? $request->is_comment : 1,
            'content' => $request->content,
            'location_name' => $request->location_name,
            'file_type' => $fileType,
        ]);

        if ($request->file && $fileType > 1) {
            $dirPath = public_path('/storage/posts/' . $post->id . '/');

            $arrFile = [];
            $arrRotate = [];
            $numb = 0;

            if ($fileType == 2) {

                if (!file_exists($dirPath)) {
                    mkdir($dirPath, 0777, true);
                }

                if (!is_array($request->file('file'))) {
                    $arrFile[0] = $request->file('file');
                    $arrRotate[0] = $request->rotate;
                } else {
                    $arrFile = $request->file('file');
                    $arrRotate = $request->rotate;
                }

                foreach ($arrFile as $file) {
                    if ($file->isValid()) {

                        $extension = $file->getClientOriginalExtension();
                        $image_name = rand(100, 999999) . time() . '_' . $numb . '.' . $extension;

                        $image = Image::make($file);

                        $image->save($dirPath . $image_name);

                        $img_url = env('APP_URL') . '/storage/posts/' . $post->id . '/' . $image_name;
                        $post->files()->create(['file_path' => $img_url, 'rotate' => $arrRotate[$numb]]);

                        $numb++;
                    }
                }
            } elseif ($fileType == 3) {

                if (!is_array($request->file('file'))) {
                    $arrFile[0] = $request->file('file');
                    $arrRotate[0] = $request->rotate;
                } else {
                    $arrFile = $request->file('file');
                    $arrRotate = $request->rotate;
                }

                foreach ($arrFile as $file) {
                    if ($file->isValid()) {
                        $filenameWithExt = $file->getClientOriginalName();
                        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $fileNameToStore = $filename . rand(100, 999999) . time() . '_' . $numb . '.' . $extension;
                        $video_url = env('APP_URL') . '/storage/posts/' . $post->id . '/' . $fileNameToStore;
                        $file->move($dirPath, $fileNameToStore);
                        $post->files()->create(['file_path' => $video_url, 'rotate' => $arrRotate[$numb]]);

                        $numb++;
                    }
                }

            } else {
                if (!is_array($request->file)) {
                    $arrFile[0] = $request->file;
                    $arrRotate[0] = $request->rotate;
                } else {
                    $arrFile = $request->file;
                    $arrRotate = $request->rotate;
                }

                foreach ($arrFile as $file) {
                    $post->files()->create(['file_path' => $file, 'rotate' => $arrRotate[$numb]]);

                    $numb++;
                }
            }
        }

        return $post;
    }

    private function timeCalculator($timeout)
    {
        $hm = explode(":", $timeout);

        $result = Carbon::parse(date('Y-m-d H:i:s'))->addHour($hm[0])->addMinutes($hm[1])->format('Y-m-d H:i:s');

        return $result;
    }

    private function postStatusUpdate(int $status, int $postId)
    {
        $postDonate = PostDonate::where('post_id', $postId)->first();
        if ($status == 1) {
            $postDonate->status = $status;
            $postDonate->chosen_user = null;
            $postDonate->message = null;
        } else {
            $postDonate->status = $status;
        }

        $postDonate->save();
    }

    public function notificationCreate(int $senderId, int $recipientId, int $postId, int $code, int $status)
    {
        $this->request->replace([
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'code' => $code,
            'post_id' => $postId,
            'status' => $status,
            'type' => 2,
        ]);

        $this->NotificationController->store($this->request);
    }

    public function notificationUpdate(int $code, int $postId, int $status, int $recipientId)
    {
        $this->request->replace([
            'code' => $code,
            'post_id' => $postId,
            'status' => $status,
            'is_readed' => 0,
        ]);

        $notificationId = $this->NotificationController->notificationId($postId, $recipientId)->id;

        $this->NotificationController->update($this->request, $notificationId);
    }

    private function upPoint(int $userId, int $tp)
    {
        $account = Account::where('user_id', $userId)->first();

        $account->update([
            'point' => $account->point + $tp,
        ]);
    }

    public function accountInfo(int $userId)
    {
        $account = Account::select('user_id', 'fcm_token')->where('user_id', $userId)->first();

        return ['userId' => $account->user_id, 'name' => $account->name, 'fcmToken' => $account->fcm_token];
    }

    private function accountReport(int $userId, int $reporterId)
    {
        $this->request->replace([
            'user_id' => $userId,
            'reporter_id' => $reporterId,
            'code' => 1,
            'status' => 2,
        ]);

        $this->ReportController->store($this->request);
    }

    private function response($post)
    {
        if (!$post->isEmpty()) {
            return response(new PostCollection($post));
        } else {
            return response(["error" => "not_found", "message" => 'Data not found.'], 404);
        }
    }

}
