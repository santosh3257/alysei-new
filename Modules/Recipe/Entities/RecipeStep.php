<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Attachment;

class RecipeStep extends Model
{
    use SoftDeletes;
    protected $PrimaryKey = 'recipe_step_id';

    
   

    
}
