<?php
namespace App\Http\FireControllers;

class Notification
{
    protected $database;
    protected $dbname = 'notification';
    private $fireNotify;

    public function __construct()
    {
        $firestore = app('firebase.firestore');
        $this->database = $firestore->database();
        $this->fireNotify = new Notify();
    }

    public function get(int $notificationId)
    {
        try {
            if (empty($convId)) {
                throw new Exception('Document Id missing');
            }

            if ($this->database->document($this->dbname . '/' . 'notification_' . $notificationId)->snapshot()->exists()) {
                return $this->database->document($this->dbname . '/' . 'notification_' . $notificationId)->snapshot()->data();
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

        $this->fireNotify->updateNoti($data['recipient']['user_id']);

        try {
            $this->database->collection($this->dbname)->document('notification_' . $data['id'])->create($data);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function update(array $data, int $notificationId)
    {
        if (empty($data) || !isset($data)) {return false;}

        $this->fireNotify->updateNoti($data['recipient']['user_id']);

        try {
            $this->database->document($this->dbname . '/' . 'notification_' . $notificationId)->set($data);
            return true;
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

}
