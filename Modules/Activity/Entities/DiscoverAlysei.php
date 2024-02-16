<?php

namespace Modules\Activity\Entities;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\DiscoveryPostCategory;

class DiscoverAlysei extends Model
{
	protected $table = 'discover_alysei';
	protected $primaryKey = 'discover_alysei_id';

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    public function category(){
        return $this->belongsTo(DiscoveryPostCategory::class, 'category_id','id');
    }

}
