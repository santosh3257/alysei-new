<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\DiscoveryPostCategory; 
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscoveryPost extends Model
{
    use SoftDeletes;
    protected $table = 'discovery_posts';
    protected $primaryKey = 'id';

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
    public function category()
    {
        return $this->belongsTo(DiscoveryPostCategory::class, 'category_id','id');
    }
}
