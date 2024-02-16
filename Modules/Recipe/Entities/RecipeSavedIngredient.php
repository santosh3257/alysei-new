<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeSavedIngredient extends Model
{
    use SoftDeletes;
    protected $PrimaryKey = 'recipe_saved_ingredient_id';
    
    public function ingredient()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->belongsTo(RecipeIngredient::class, 'ingredient_id','recipe_ingredient_id')->select('recipe_ingredient_id','name','title_'.$lang.' as title','image_id','parent','featured','priority');
    }

    
}
