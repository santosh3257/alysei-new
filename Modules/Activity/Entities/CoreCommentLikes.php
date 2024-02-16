<?php

namespace Modules\Activity\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class CoreCommentLikes extends Model
{
	protected $primaryKey = 'id';
    protected $table = 'core_comment_likes';
    protected $fillable = ['id','resource_id','poster_type','poster_id','comment_id','created_at','updated_at'];

    public function poster()
    {
        return $this->belongsTo(User::class, 'poster_id','user_id');
    }
}
