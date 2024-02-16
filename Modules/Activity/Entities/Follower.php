<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class Follower extends Model
{
	protected $primaryKey = 'followers';
    protected $fillable = [];

    public function followed_by()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function following_by()
    {
        return $this->belongsTo(User::class, 'follow_user_id','user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'follow_user_id','user_id');
    }

}
