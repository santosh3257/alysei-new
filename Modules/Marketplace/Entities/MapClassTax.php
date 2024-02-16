<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MarketplaceTax;

class MapClassTax extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_map_calss_taxes';
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function getTaxDetail(){
        return $this->belongsTo(MarketplaceTax::class, 'tax_id','tax_id');
    }
}
