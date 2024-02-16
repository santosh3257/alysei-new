<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MarketplaceOrderItem;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceOrderTransaction;
use Modules\Marketplace\Entities\MarketplaceOrderShippingAddress;
use Modules\Marketplace\Entities\MarketplaceOrderUserAddress;
use Modules\Marketplace\Entities\MarketplaceStore;


class MarketplaceOrder extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_orders';
    protected $primaryKey = 'order_id';
    protected $fillable = [];

    public function productItemInfo(){
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id','order_id');
    }

    public function transactionInfo(){
        return $this->belongsTo(MarketplaceOrderTransaction::class, 'order_id','order_id')->select('id','order_id','transaction_id','paid_amount','status');
    }

    public function sellerInfo(){
        return $this->belongsTo(User::class, 'seller_id','user_id')->select('user_id','name','email','country_code','phone','company_name','email','address1','address');
    }

    public function buyerInfo(){
        return $this->belongsTo(User::class, 'buyer_id','user_id')->select('user_id','name','email','country_code','phone','company_name','address1','address','country_id');
    }

    public function shippingAddress(){
        return $this->belongsTo(MarketplaceOrderShippingAddress::class, 'shipping_id','id');
    }

    public function billingAddress(){
        return $this->belongsTo(MarketplaceOrderUserAddress::class, 'billing_id','id');
    }

    public function getStore(){
        return $this->belongsTo(MarketplaceStore::class, 'store_id','marketplace_store_id');
    }
}
