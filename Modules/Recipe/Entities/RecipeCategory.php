<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class RecipeCategory extends Model
{
    protected $PrimaryKey = 'recipe_category_id';

    public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
   

    
}
