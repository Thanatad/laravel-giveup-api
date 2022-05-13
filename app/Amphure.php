<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Amphure extends Model
{
    protected $guarded = [];

    public function provinces()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'amphure_id');
    }

}
