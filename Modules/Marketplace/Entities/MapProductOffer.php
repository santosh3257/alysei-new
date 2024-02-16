<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\Marketplace\Entities\MarketplaceProduct;

class MapProductOffer extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_map_product_offers';
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function offerInfo(){
        return $this->belongsTo(ProductOffer::class, 'offer_id','offer_id')->select('offer_id','offer_name');
    }

    public function productInfo(){
        return $this->belongsTo(MarketplaceProduct::class, 'product_id','marketplace_product_id');
    }
}
