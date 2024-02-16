<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceOrderItemTax extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_order_item_taxes';
    protected $primaryKey = 'id';
    protected $fillable = [];
}
