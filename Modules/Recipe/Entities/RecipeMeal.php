<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class RecipeMeal extends Model
{
    protected $PrimaryKey = 'recipe_meal_id';

    public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
    
    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    
}
