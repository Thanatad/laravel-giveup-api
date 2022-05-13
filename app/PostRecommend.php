<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostRecommend extends Model
{
    protected $fillable = ['post_id'];
    protected $hidden = ['created_at', 'updated_at'];
}
