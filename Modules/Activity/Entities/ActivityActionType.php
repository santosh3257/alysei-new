<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;

class ActivityActionType extends Model
{
	protected $table = 'activity_action_types';
	protected $primaryKey = 'activity_action_type_id';
    
}
