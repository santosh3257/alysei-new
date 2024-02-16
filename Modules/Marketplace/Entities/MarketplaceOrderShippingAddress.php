<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceOrderShippingAddress extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_order_shipping_addresses';
    protected $primaryKey = 'id';
    protected $fillable = [];
}
