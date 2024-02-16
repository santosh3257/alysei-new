<?php

namespace Modules\Marketplace\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class MarketplaceTax extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'tax_id';
    protected $fillable = ['tax_id','user_id','tax_name','tax_rate','tax_type','deleted_at','created_at','updated_at'];

}
