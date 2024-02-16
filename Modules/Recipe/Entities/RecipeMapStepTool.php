<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Attachment;

class RecipeMapStepTool extends Model
{
    use SoftDeletes;
    protected $PrimaryKey = 'recipe_map_steps_tool_id';
    protected $table = 'recipe_map_steps_tools';

    /*public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }*/
   

    
}
