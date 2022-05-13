<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
class ChatConversations extends Model
{
    protected $guarded = [];
    protected $appends = ['message_1_last','message_1_unseen','message_1_unseen_limit','message_2_last','message_2_unseen','message_2_unseen_limit'];

    public function message()
    {
        return $this->hasMany(ChatMessage::class, 'chat_conversation_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function account1()
    {
        return $this->belongsTo(Account::class,'user_id_one');
    }

    public function account2()
    {
        return $this->belongsTo(Account::class,'user_id_two');
    }

    public function messageLast()
    {
        return $this->message()->where('user_id', '<>', Auth::user()->id)->orderBy('id', 'desc')->limit(2);
    }

    public function messageUnseen()
    {
        return $this->message()->where('user_id', '<>', Auth::user()->id)->where('is_seen',0);
    }

    public function messageUnseenLimit()
    {
        return $this->message()->where('user_id', '<>', Auth::user()->id)->where('is_seen',0)->orderBy('id', 'desc')->limit(2);
    }

    public function getMessage1LastAttribute()
    {
        return $this->message()->with('account')->where('user_id', $this->user_id_two)->orderBy('id', 'desc')->limit(2)->get();
    }

    public function getMessage1UnseenAttribute()
    {
        return $this->message()->where('user_id', $this->user_id_two)->where('is_seen', 0)->get();;
    }

    public function getMessage1UnseenLimitAttribute()
    {
        return $this->message()->with('account')->where('user_id', $this->user_id_two)->where('is_seen', 0)->orderBy('id', 'desc')->limit(2)->get();
    }

    public function getMessage2LastAttribute()
    {
        return $this->message()->with('account')->where('user_id', $this->user_id_one)->orderBy('id', 'desc')->limit(2)->get();
    }

    public function getMessage2UnseenAttribute()
    {
        return $this->message()->where('user_id', $this->user_id_one)->where('is_seen', 0)->get();
    }

    public function getMessage2UnseenLimitAttribute()
    {
        return $this->message()->with('account')->where('user_id', $this->user_id_one)->where('is_seen', 0)->orderBy('id', 'desc')->limit(2)->get();
    }


}
