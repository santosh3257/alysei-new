<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class PreferenceMapIntolerance extends Model
{
    public function intolerance()
    {
        return $this->belongsTo(RecipeFoodIntolerance::class, 'intolerance_id','recipe_food_intolerance_id');
    }

    
}
