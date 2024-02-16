<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;
use App\Attachment;

class RecipeMapStepIngredient extends Model
{
    use SoftDeletes;
    protected $PrimaryKey = 'recipe_map_steps_ingredient_id';
    protected $table = 'recipe_map_steps_ingredients';

    /*public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }*/
   

    
}
