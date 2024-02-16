<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
class Hub extends Model
{
    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id','id');
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id','id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
