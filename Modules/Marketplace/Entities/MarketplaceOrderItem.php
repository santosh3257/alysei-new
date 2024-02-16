<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MapProductOffer;

class MarketplaceOrderItem extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_order_items';
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function productInfo(){
        return $this->belongsTo(MarketplaceProduct::class, 'product_id','marketplace_product_id')->select('marketplace_product_id','title','slug','product_category_id')->withTrashed();
    }
    
    public function offerMapInfo(){
        return $this->belongsTo(MapProductOffer::class, 'offer_map_id','id')->select('id','offer_id');
    }
   
    
}
