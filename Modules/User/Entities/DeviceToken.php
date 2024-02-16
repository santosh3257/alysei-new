<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = ['user_id','device_type','device_token'];
}
