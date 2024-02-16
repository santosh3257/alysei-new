<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class Intensity extends Model
{
    protected $primaryKey = 'intensity_id';
    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }*/

}
