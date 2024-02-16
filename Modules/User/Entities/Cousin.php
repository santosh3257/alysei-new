<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class Cousin extends Model
{
    protected $primaryKey = 'cousin_id';

    public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

}
