<?php
namespace App\Http\FireControllers;
use App\ChatMessage as mChatMessage;

class ChatMessage
{
    protected $database;
    protected $dbnameI = 'chat_conversation';
    protected $dbnameII = 'chat_message';
    private $fireNotify;

    public function __construct()
    {
        $firestore = app('firebase.firestore');
        $this->database = $firestore->database();
        $this->fireNotify = new Notify();
    }

    public function get(int $convId, int $messageId)
    {
        try {
            if (empty($convId) || empty($messageId)) {
                throw new Exception('Document Id missing');
            }

            if ($this->database->document($this->dbnameI . '/' . 'conversation_' . $convId . '/' . $this->dbnameII . '/' . 'message_' . $messageId)->snapshot()->exists()) {
                return $this->database->document($this->dbnameI . '/' . 'conversation_' . $convId . '/' . $this->dbnameII . '/' . 'message_' . $messageId)->snapshot()->data();
            } else {
                throw new Exception('Document are not exists');
            }

        } catch (Exception $exception) {
            return $exception->getMessage();
        }

    }

    public function create(array $data)
    {
        if (empty($data) || !isset($data)) {return false;}

        $targetUser = $this->targetUserId($data['chat_conversation_id'],$data['user_id']);

        if($targetUser) $this->fireNotify->updateChat($targetUser->user_id);

        try {
            $this->database->collection($this->dbnameI)->document('conversation_' . $data['chat_conversation_id'])->collection($this->dbnameII)->document('message_' . $data['id'])->create($data);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function update(array $data, int $convId, int $messageId)
    {
        if (empty($data) || !isset($data)) {return false;}

        $targetUser = $this->targetUserId($data['chat_conversation_id'],$data['user_id']);

        if($targetUser) $this->fireNotify->updateChat($targetUser->user_id);

        try {
            $this->database->document($this->dbnameI . '/' . 'conversation_' . $convId . '/' . $this->dbnameII . '/' . 'message_' . $messageId)->set($data);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function targetUserId(int $ccId, int $userId){
        return mChatMessage::select('user_id')->where('chat_conversation_id', $ccId)->where('user_id', '<>', $userId)->first();
    }

}
