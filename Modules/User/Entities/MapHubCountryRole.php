<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class MapHubCountryRole extends Model
{
	protected $table = 'map_hub_country_roles';
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id','id');
    }
}
