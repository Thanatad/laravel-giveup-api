<?php
namespace App\Http\FireControllers;

use App\ChatConversations;
use App\Http\Resources\ChatConversations\ChatConversations as ChatConversationsResource;

class ChatConversation
{
    protected $database;
    protected $dbname = 'chat_conversation';

    public function __construct()
    {
        $firestore = app('firebase.firestore');
        $this->database = $firestore->database();
    }

    public function get(int $convId)
    {
        try {
            if (empty($convId)) {
                throw new Exception('Document Id missing');
            }

            if ($this->database->document($this->dbname . '/' . 'conversation_' . $convId)->snapshot()->exists()) {
                return $this->database->document($this->dbname . '/' . 'conversation_' . $convId)->snapshot()->data();
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

        try {
            $this->database->collection($this->dbname)->document('conversation_' . $data['id'])->set(['id' => $data['id']]);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function update(array $data, int $convId)
    {
        if (empty($data) || !isset($data)) {return false;}

        try {
            $this->database->document($this->dbname . '/' . 'conversation_' . $convId)->set($data);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function updateConversation(int $convId)
    {
        $response = ChatConversations::with('account1','account2','post.files')->where('id', $convId)->first();
        $this->update(json_decode((new ChatConversationsResource($response))->toJson(), true), $convId);
    }

}
