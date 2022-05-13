<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InviteLog extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class,'user_id');
    }
}
