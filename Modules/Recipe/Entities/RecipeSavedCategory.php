<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeSavedCategory extends Model
{
    use SoftDeletes;
    protected $PrimaryKey = 'recipe_saved_category_id';
   

    
}
