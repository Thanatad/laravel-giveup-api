<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Geographie extends Model
{
    protected $guarded = [];

    public function provinces()
    {
        return $this->hasMany(Province::class, 'geography_id');
    }
}
