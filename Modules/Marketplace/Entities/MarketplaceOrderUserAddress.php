<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceOrderUserAddress extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_user_address';
    protected $primaryKey = 'id';
    protected $fillable = [];
}
