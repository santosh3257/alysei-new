<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class ChangeEmailRequest extends Model
{
    protected $fillable = ['user_id','email','device_token','created_at','updated_at'];
}
