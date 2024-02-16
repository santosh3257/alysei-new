<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class RecipeReviewRating extends Model
{
    protected $PrimaryKey = 'recipe_review_rating_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
   

    
}
