<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class ReportUser extends Model
{
    protected $primaryKey = 'id';

    public function report_by_user_info()
    {
        return $this->belongsTo(User::class, 'report_by','user_id')->select('user_id','first_name','last_name','name','company_name','role_id');
    }
    public function report_to_user_info()
    {
        return $this->belongsTo(User::class, 'user_id','user_id')->select('user_id','first_name','last_name','name','company_name','role_id');
    }
}
