<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class UserTempHub extends Model
{

	protected $table = "temp_hubs";

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id','id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id','id');
    }

}
