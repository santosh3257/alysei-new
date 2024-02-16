<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\User\Entities\Cousin;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Attachment;
use Cviebrock\EloquentSluggable\Sluggable;

class Recipe extends Model
{
    use SoftDeletes;
    use Sluggable;
    protected $primaryKey = 'recipe_id';

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    public function meal()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->belongsTo(RecipeMeal::class, 'meal_id','recipe_meal_id')->select('recipe_meal_id','name_'.$lang.' as name','featured','image_id','priority','created_at','updated_at');
    }

    public function course()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->belongsTo(RecipeCourse::class, 'course_id','recipe_course_id')->select('recipe_course_id','name_'.$lang.' as name','featured','priority','created_at','updated_at');
    }

    public function cousin()
    {
        return $this->belongsTo(Cousin::class, 'cousin_id','cousin_id');
    }

    public function region()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->belongsTo(RecipeRegion::class, 'region_id','recipe_region_id')->select('recipe_region_id','cousin_id','name_'.$lang.' as name','image_id','featured','priority');
    }

    public function ingredients()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->hasMany(RecipeSavedIngredient::class, 'recipe_id','recipe_id')->select('recipe_ingredient_id','name','title_'.$lang.' as title','image_id','parent','featured','priority');
    }

    public function diet()
    {
        $language = request()->locale;
        $lang = 'en';
        if(!empty($language)){
            $lang = $language;
        }
        return $this->belongsTo(RecipeDiet::class, 'diet_id','recipe_diet_id')->select('recipe_diet_id','name_'.$lang.' as name','image_id','priority');
    }

    public function intolerance()
    {
        return $this->belongsTo(RecipeFoodIntolerance::class, 'intolerance_id','recipe_food_intolerance_id');
    }

    public function cookingskill()
    {
        return $this->belongsTo(RecipeCookingSkill::class, 'cooking_skill_id','recipe_cooking_skill_id');
    }
   

    
}
