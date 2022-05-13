<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    public $incrementing = false;

    protected $fillable = ['post_id', 'user_id', 'like'];
    protected $primaryKey = ['post_id', 'user_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
