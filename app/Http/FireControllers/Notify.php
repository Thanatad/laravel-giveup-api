<?php
namespace App\Http\FireControllers;

use App\ChatConversations;
use App\ChatMessage;
use App\Notification;

class Notify
{
    protected $database;
    protected $dbchat = 'user_chat_notify';
    protected $dbnoti = 'user_notification_notify';

    public function __construct()
    {
        $firestore = app('firebase.firestore');
        $this->database = $firestore->database();
    }

    public function get(int $userId, string $dbname)
    {
        try {
            if (empty($userId) || empty($userId)) {
                throw new Exception('Document Id missing');
            }

            if ($this->database->document($dbname . '/' . 'user_' . $userId)->snapshot()->exists()) {
                return $this->database->document($dbname . '/' . 'user_' . $userId)->snapshot()->data();
            } else {
                throw new Exception('Document are not exists');
            }

        } catch (Exception $exception) {
            return $exception->getMessage();
        }

    }

    public function update(int $userId, string $dbname, array $data)
    {
        if (empty($data) || !isset($data)) {return false;}

        try {
            $this->database->document($dbname . '/' . 'user_' . $userId)->set($data);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function getChat(int $userId)
    {
        return $this->get($userId, $this->dbchat);
    }

    public function getNoti(int $userId)
    {
        return $this->get($userId, $this->dbnoti);
    }

    public function updateChat(int $userId)
    {
        $message1 = ChatMessage::whereIn('chat_conversation_id', $this->listConversation($userId, 1))->where('user_id', '<>', $userId)->where('is_seen', 0)->get('id');
        $message2 = ChatMessage::whereIn('chat_conversation_id', $this->listConversation($userId, 2))->where('user_id', '<>', $userId)->where('is_seen', 0)->get('id');

        $arrayData = ['isReadAllChat' => true, 'isReadAllChatDonate' => true];

        if (!$message1->isEmpty()) $arrayData['isReadAllChat'] = false;
        if (!$message2->isEmpty()) $arrayData['isReadAllChatDonate'] = false;

        return $this->update($userId, $this->dbchat, $arrayData);
    }

    public function updateNoti(int $userId)
    {
        $notification1 = Notification::where('recipient_id', $userId)->where('type', 1)->where('is_readed', 0)->get('id');
        $notification2 = Notification::where('recipient_id', $userId)->where('type', 2)->where('is_readed', 0)->get('id');

        $arrayData = ['isReadAllNotification' => true, 'isReadAllNotificationDonate' => true];

        if (!$notification1->isEmpty()) $arrayData['isReadAllNotification'] = false;
        if (!$notification2->isEmpty()) $arrayData['isReadAllNotificationDonate'] = false;

        return $this->update($userId, $this->dbnoti, $arrayData);
    }

    private function listConversation(int $userId, int $type)
    {
        return ChatConversations::where(function ($q) use ($userId) {
            $q->where('user_id_one', $userId)->orWhere('user_id_two', $userId);
        })->where('type', $type)->pluck('id');
    }

}
