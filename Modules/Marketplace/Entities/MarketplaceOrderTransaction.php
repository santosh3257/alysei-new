<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceOrderItemTax;
use Modules\Marketplace\Entities\MarketplaceOrderItem;
use Modules\Marketplace\Entities\MarketplaceOrder;
use Modules\Marketplace\Entities\PaymentSetting;

class MarketplaceOrderTransaction extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_order_transactions';
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function sellerInfo(){
        return $this->belongsTo(User::class, 'seller_id','user_id')->select('user_id','name','email','country_code','phone','company_name','email','address1','address');
    }

    public function buyerInfo(){
        return $this->belongsTo(User::class, 'buyer_id','user_id')->select('user_id','name','email','country_code','phone','company_name','address1','address');
    }

    public function orderItemInfo(){
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id','order_id');
    }

    public function orderInfo(){
        return $this->belongsTo(MarketplaceOrder::class, 'order_id','order_id');
    }

    public function bankInfo(){
        return $this->belongsTo(PaymentSetting::class, 'seller_id','user_id');
    }
}
