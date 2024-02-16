<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;

class ActivityAttachment extends Model
{
	protected $primaryKey = 'activity_attachment_id';
    protected $fillable = [];

    public function attachment_link()
    {
        return $this->belongsTo(ActivityAttachmentLink::class, 'id','activity_attachment_link_id');
    }
}
