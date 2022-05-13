<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
class Account extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'user_id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function followings()
    {
        return $this->belongsToMany(Account::class, 'followers', 'follower_user_id', 'following_user_id')->where('is_approved', 1)->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(Account::class, 'followers', 'following_user_id', 'follower_user_id')->where('is_approved', 1)->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function storys()
    {
        return $this->hasMany(Story::class, 'user_id');
    }

    public function attentions()
    {
        return $this->belongsToMany(Attention::class, 'account_attentions', 'user_id', 'attention_id')->withTimestamps();
    }

    public function hasFollow(){
        return $this->followers()->where('follower_user_id',Auth::user()->id);
    }
}
