<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'user_id';
    protected $hidden = ['created_at', 'updated_at'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'user_id');
    }
}
