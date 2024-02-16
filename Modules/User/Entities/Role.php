<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
class Role extends Model
{
    protected $fillable = ['name','type','slug','image_id'];

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
