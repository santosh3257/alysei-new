<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\UserFieldOption;

class UserFieldValue extends Model
{

    public function user_selected_fields()
    {
        return $this->belongsTo(UserFieldOption::class, 'value','user_field_option_id')->select('option');
    }
}