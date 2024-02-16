<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class MapCountryRole extends Model
{
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id','id');
    }
}
