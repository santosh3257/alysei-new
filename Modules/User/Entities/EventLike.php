<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class EventLike extends Model
{
    protected $fillable = [];

    public function user() 
    { 
        return $this->hasOne(User::class, 'user_id','user_id'); 
    }
}
