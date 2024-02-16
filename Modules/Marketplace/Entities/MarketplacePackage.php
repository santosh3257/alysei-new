<?php

namespace Modules\Marketplace\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketplacePackage extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'marketplace_package_id';
    protected $fillable = [];
}
