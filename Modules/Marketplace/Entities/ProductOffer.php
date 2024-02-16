<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MapProductOffer;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\Incoterms;

class ProductOffer extends Model
{
    use SoftDeletes;
    protected $table = 'marketplace_product_offers';
    protected $primaryKey = 'offer_id';
    protected $fillable = [
        'offer_id',
        'seller_id',
        'buyer_id',
        'product_id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function getMapOffer(){
        return $this->hasMany(MapProductOffer::class, 'offer_id','offer_id');
    }

    public function getSellerInfo(){
        return $this->hasMany(User::class, 'user_id','seller_id')->select('user_id','company_name');
    }
    public function getBuyerInfo(){
        return $this->hasMany(User::class, 'user_id','buyer_id')->select('user_id','company_name');
    }

    public function getIncoterm(){
        return $this->belongsTo(Incoterms::class, 'icoterm_id','id')->select('id','incoterms');
    }
}
