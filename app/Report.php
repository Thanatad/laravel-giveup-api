<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = [];

    public function codes()
    {
        return $this->belongsTo(ReportCode::class, 'code');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(Account::class, 'reporter_id');
    }
}
