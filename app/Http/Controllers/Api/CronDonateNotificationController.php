<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Controller;
use App\Http\FireControllers\PushNotification;
use App\NotificationCode;
use App\PostDonate;
use Carbon;
use Illuminate\Http\Request;

class CronDonateNotificationController extends Controller
{
    protected $PostController;
    private $code;
    private $request;
    private $pushNotification;

    public function __construct(PostController $PostController)
    {
        $this->request = new Request;
        $this->PostController = $PostController;
        $this->pushNotification = new PushNotification();
        $this->code = NotificationCode::get();
    }

    public function index()
    {
        $donate = PostDonate::where('status', 4)->get();

        foreach ($donate as $item) {
            switch ($this->calDateDiff($item->updated_at)) {
                case 7:
                    $receiver = $this->PostController->accountInfo($item->chosen_user);
                    $this->pushNotification->message($receiver['userId'], $receiver['fcmToken'], 'แจ้งเตือนใหม่', $this->code[15]->name, ['type' => 'DONATEPOST', 'post_id' => $item->post_id, 'status' => 3, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

                    $this->PostController->notificationCreate($item->post->user_id, $item->chosen_user, $item->post_id, 16, 3);
                    break;
                case 9:
                    $receiver = $this->PostController->accountInfo($item->chosen_user);
                    $this->pushNotification->message($receiver['userId'], $receiver['fcmToken'], 'แจ้งเตือนใหม่', $this->code[16]->name, ['type' => 'DONATEPOST', 'post_id' => $item->post_id, 'status' => 3, 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']);

                    $this->PostController->notificationCreate($item->post->user_id, $item->chosen_user, $item->post_id, 17, 3);
                    break;
                case 10:
                    $this->request->replace(['status' => 5]);
                    $this->PostController->donateUpdate($this->request, $item->post_id);
                    break;
                default:
                    break;
            }
        }

        return response(['success' => 'Successfully'], 200);
    }

    private function calDateDiff($datetime)
    {
        $date = Carbon::parse($datetime->format('Y-m-d'));
        $now = Carbon::parse(date('Y-m-d'));

        $diff = $date->diffInDays($now);
        return $diff;
    }

}
