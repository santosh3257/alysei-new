<?php

namespace Modules\User\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserField extends Model
{
	use SoftDeletes;
    
}
