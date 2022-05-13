<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObjectCategory extends Model
{
    protected $fillable = ['name'];
    protected $hidden = ['pivot'];
    public $timestamps = false;

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_donate_object_categories', 'objcategory_id','post_id')->withTimestamps();
    }
}
