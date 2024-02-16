<?php

namespace Modules\Activity\Entities;
use Modules\Activity\Entities\ActivityAttachmentLink;

use Illuminate\Database\Eloquent\Model;

class ActivityAttachmentLink extends Model
{
	protected $primaryKey = 'activity_attachment_link_id';
    protected $fillable = [];

    /*public function attachment_link()
    {
        return $this->belongsTo(ActivityAttachmentLink::class, 'id','activity_attachment_link_id');
    }*/
}
