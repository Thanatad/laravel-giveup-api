<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $guarded = [];

    public function amphures()
    {
        return $this->belongsTo(Amphure::class,'amphure_id');
    }
}
