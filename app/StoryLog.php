<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoryLog extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
