<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;

class ConnectFollowPermission extends Model
{
	protected $table = 'connect_follow_permissions';
	protected $primaryKey = 'connect_follow_permission_id';
    protected $fillable = [];
}
