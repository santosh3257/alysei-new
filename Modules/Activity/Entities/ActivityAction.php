<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityAction extends Model
{
    use SoftDeletes;
	protected $primaryKey = 'activity_action_id';
    protected $fillable = [];

    public function attachments()
    {
        return $this->hasMany(ActivityAttachment::class, 'action_id','activity_action_id');
    }

    public function subject_id()
    {
        return $this->belongsTo(User::class, 'subject_id','user_id');
    }
    public function subjectId()
    {
        return $this->belongsTo(User::class, 'subject_id','user_id');
    }
}
