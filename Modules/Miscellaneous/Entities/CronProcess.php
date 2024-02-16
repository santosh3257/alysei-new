<?php

namespace Modules\Miscellaneous\Entities;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class CronProcess extends Model
{
    protected $table = 'notification_cron_process';
    protected $primaryKey = 'notification_cron_process_id';
    protected $fillable = ['notification_cron_process_id','cron_job_id','user_id','status','created_at','updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}
