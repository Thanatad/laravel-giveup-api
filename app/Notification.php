<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function sender()
    {
        return $this->belongsTo(Account::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Account::class, 'recipient_id');
    }

    public function codes()
    {
        return $this->belongsTo(NotificationCode::class, 'code');
    }
}
