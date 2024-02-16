<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class WalkThroughPoint extends Model
{
    protected $fillable = ['title_en','title_it','description_it','description_en','icon_id','walk_through_screen_id'];

    public function attachment(){
        return $this->belongsTo(Attachment::class, 'icon_id','id');
    }
}
