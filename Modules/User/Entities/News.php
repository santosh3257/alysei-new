<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Attachment;
class News extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'news_id';
    protected $table = 'news';
    protected $fillable = ['new_id','title','slug','description','image_id','status','deleted_at','created_at','updated_at'];


    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
