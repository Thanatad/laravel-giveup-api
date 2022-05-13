<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $guarded = [];

    public function amphures()
    {
        return $this->hasMany(Amphure::class, 'province_id');
    }

    public function geographies()
    {
        return $this->belongsTo(Geographie::class, 'geography_id');
    }

}
