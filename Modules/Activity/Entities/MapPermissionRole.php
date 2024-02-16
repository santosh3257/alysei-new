<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;

class MapPermissionRole extends Model
{
	protected $table = 'map_permission_roles';
	protected $primaryKey = 'map_permission_role_id';
    protected $fillable = [];
}
