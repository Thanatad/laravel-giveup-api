<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostFile extends Model
{
    protected $fillable = ['file_path', 'post_id', 'rotate'];
    public $timestamps = false;
}
