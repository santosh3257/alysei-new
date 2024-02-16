<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class PreferenceMapDiet extends Model
{
    public function diet()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->belongsTo(RecipeDiet::class, 'diet_id','recipe_diet_id')->select('recipe_diet_id','name_'.$lang.' as name','image_id','priority');
    }

    
}
