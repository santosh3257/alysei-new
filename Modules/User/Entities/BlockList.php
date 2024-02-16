<?php

namespace Modules\User\Entities;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;

class BlockList extends Model
{
	protected $primaryKey = 'block_list_id';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function blockuser()
    {
        return $this->belongsTo(User::class, 'block_user_id','user_id');
    }
}
