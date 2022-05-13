<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    public function files()
    {
        return $this->hasMany(PostFile::class, 'post_id', 'id');
    }

    // public function comments()
    // {
    //     return $this->hasMany(PostComment::class, 'post_id', 'id');
    // }

    public function commentParents()
    {
        return $this->hasMany(PostComment::class, 'post_id')->where('parent_id', 0)->limit(2)->latest();
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id')->where('is_deleted', 0);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id', 'id')->where('like', 1);
    }

    public function likeLimit()
    {
        return $this->hasMany(PostLike::class, 'post_id', 'id')->where('like', 1)->limit(5)->latest();
    }

    public function shareLog()
    {
        return $this->hasMany(ShareLog::class, 'post_id', 'id');
    }

    public function postDonate()
    {
        return $this->hasOne(PostDonate::class, 'post_id', 'id');
    }

    public function objectCategories()
    {
        return $this->belongsToMany(ObjectCategory::class, 'post_donate_object_categories', 'post_id', 'objcategory_id')->withTimestamps();
    }

}
