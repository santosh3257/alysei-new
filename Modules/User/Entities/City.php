<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id','id');
    }
}
