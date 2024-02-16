<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
class State extends Model
{
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }

    public function flag_id()
    {
        return $this->belongsTo(Attachment::class, 'flag_id','id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'flag_id','id');
    }
}
