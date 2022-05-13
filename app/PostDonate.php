<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostDonate extends Model
{
    protected $guarded = [];
    // protected $hidden = ['created_at','updated_at'];
    protected $primaryKey = 'post_id';

    public function account()
    {
        return $this->belongsTo(Account::class, 'chosen_user');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function postDonateReasonLimit()
    {
        return $this->hasMany(PostDonateReason::class, 'post_id', 'post_id')->limit(2)->latest();
    }
    public function postDonateReason()
    {
        return $this->hasMany(PostDonateReason::class, 'post_id', 'post_id');
    }


}
