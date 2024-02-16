<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MapClassTax;

class MarketplaceTaxClasses extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'tax_class_id';
    protected $fillable = [];

    public function getTaxClasses(){
        return $this->hasMany(MapClassTax::class, 'class_id','tax_class_id');
    }
}
