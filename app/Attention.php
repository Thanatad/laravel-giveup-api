<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attention extends Model
{
    protected $fillable = ['name'];
    protected $hidden = ['pivot'];
    public $timestamps = false;

}
