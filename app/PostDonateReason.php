<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostDonateReason extends Model
{
    public $incrementing = false;

    protected $fillable = ['post_id', 'user_id','reason','is_readed'];
    protected $primaryKey = ['post_id', 'user_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

}
