<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class PreferenceMapCookingSkill extends Model
{
    public function cooking_skill()
    {
        return $this->belongsTo(RecipeCookingSkill::class, 'cooking_skill_id','recipe_cooking_skill_id');
    }

    
}
