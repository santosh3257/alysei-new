<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class ActivityLike extends Model
{
	protected $primaryKey = 'activity_like_id';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'poster_id','user_id');
    }
}
