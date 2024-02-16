<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\State;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Cviebrock\EloquentSluggable\Sluggable;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\Incoterms;
class MarketplaceStore extends Model
{
    use SoftDeletes;
    use Sluggable;
    protected $primaryKey = 'marketplace_store_id';
    protected $fillable = [];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function logo_id()
    {
        return $this->belongsTo(Attachment::class, 'logo_id','id');
    }

    public function region()
    {
        return $this->belongsTo(State::class, 'store_region','id');
    }

    public function store_gallery()
    {
        return $this->hasMany(MarketplaceStoreGallery::class, 'marketplace_store_id','marketplace_store_id');
    }

    public function logo()
    {
        return $this->belongsTo(Attachment::class, 'logo_id','id');
    }

    public function banner()
    {
        return $this->belongsTo(Attachment::class, 'banner_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function firstProduct(){
        return $this->belongsTo(MarketplaceProduct::class, 'first_product_id','marketplace_product_id')->withTrashed();
    }

    public function getIncoterm(){
        return $this->belongsTo(Incoterms::class, 'incoterm_id','id');
    }
}

