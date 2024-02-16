<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class Preference extends Model
{
    protected $PrimaryKey = 'preference_id';

    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    
}
