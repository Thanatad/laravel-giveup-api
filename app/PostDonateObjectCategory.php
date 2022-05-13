<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostDonateObjectCategory extends Model
{
    protected $fillable = ['post_id', 'objcategory_id'];
    protected $hidden = ['created_at', 'updated_at'];
}
