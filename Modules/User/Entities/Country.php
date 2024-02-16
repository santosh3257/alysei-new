<?php

namespace Modules\User\Entities;
use App\Attachment;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    //protected $fillable = [];
    public function flag_id()
    {
        return $this->belongsTo(Attachment::class, 'flag_id','id');
    }

    //protected $fillable = [];
    public function flagImg()
    {
        return $this->belongsTo(Attachment::class, 'flag_id','id');
    }
}
