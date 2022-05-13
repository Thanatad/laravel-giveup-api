<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountAttention extends Model
{
    protected $fillable = ['user_id', 'attention_id'];
    protected $hidden = ['created_at', 'updated_at'];
}
