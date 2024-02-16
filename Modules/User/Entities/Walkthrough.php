<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Attachment;
class walkthrough extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'walk_through_screen_id';
    protected $table = 'walk_through_screens';
    protected $fillable = ['walk_through_screen_id','role_id','step','title_en','description_en','title_it','description_it','image_id','tab','order','deleted_at','created_at','updated_at'];


    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}

