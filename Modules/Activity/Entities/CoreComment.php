<?php

namespace Modules\Activity\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class CoreComment extends Model
{ 
    use SoftDeletes;
	protected $primaryKey = 'core_comment_id';
    protected $fillable = [];

    public function poster()
    {
        return $this->belongsTo(User::class, 'poster_id','user_id');
    }
}
