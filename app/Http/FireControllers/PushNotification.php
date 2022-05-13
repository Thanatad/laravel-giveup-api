<?php
namespace App\Http\FireControllers;

use App\UserSetting;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;

class PushNotification
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function push(array $notification, array $data, string $deviceToken)
    {
        try {
            $message = CloudMessage::fromArray([
                'token' => $deviceToken,
                'notification' => $notification,
                'data' => $data,
            ]);

            $this->messaging->send($message);

        } catch (NotFound $e) {
            return true;
        }

    }

    public function message(int $userId, string $fcmToken, string $title, string $message, array $data)
    {
        if ($this->hasPushNotification($userId) && $fcmToken) {

            $notification = [
                'body' => $message, 'title' => $title,
            ];

            $this->push($notification, $data, $fcmToken);
        }
    }

    private function hasPushNotification($userId)
    {
        return UserSetting::select('is_push_notification')->where('user_id', $userId)->first()->is_push_notification;
    }

}
