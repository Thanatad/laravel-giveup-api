<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportPost extends Model
{
    protected $guarded = [];

    public function codes()
    {
        return $this->belongsTo(ReportPostCode::class, 'code');
    }

    public function reporter()
    {
        return $this->belongsTo(Account::class, 'reporter_id');
    }
}
