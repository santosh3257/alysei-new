<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class PreferenceMapUser extends Model
{

    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    
}
