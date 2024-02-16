<?php

namespace Modules\Miscellaneous\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class CronJob extends Model
{
    protected $table = 'cron_jobs';

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'attachment_id','id');
    }
}
