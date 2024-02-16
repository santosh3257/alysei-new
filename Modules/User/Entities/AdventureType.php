<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class AdventureType extends Model
{
    protected $primaryKey = 'adventure_types';
    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }*/

}
