<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\Activity\Entities\ActivityAction;

class ActivitySpam extends Model
{
    protected $table = 'activity_spams';
    protected $primaryKey = 'activity_spam_id';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'report_by','user_id');
    }
    public function activityData()
    {
        return $this->belongsTo(ActivityAction::class, 'activity_action_id','activity_action_id')->withTrashed();
    }
}
