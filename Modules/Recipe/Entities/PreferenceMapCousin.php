<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\Cousin;
use App\Attachment;

class PreferenceMapCousin extends Model
{
    public function cousin()
    {
        return $this->belongsTo(Cousin::class, 'cousin_id','cousin_id');
    }

    
}
