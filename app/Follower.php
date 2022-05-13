<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $fillable = ['following_user_id', 'follower_user_id','is_approved'];

}
