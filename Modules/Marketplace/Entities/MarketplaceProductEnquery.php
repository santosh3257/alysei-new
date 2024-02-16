<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\User;
use Modules\Marketplace\Entities\MarketplaceProduct;

class MarketplaceProductEnquery extends Model
{
    protected $primaryKey = 'marketplace_product_enquery_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id','user_id')->with('profile_img')->select(['user_id', 'name','email','first_name','middle_name','last_name','company_name','restaurant_name','avatar_id']);
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'producer_id','user_id')->with('profile_img')->select(['user_id', 'name','email','first_name','middle_name','last_name','company_name','restaurant_name','avatar_id']);
    }
    public function product()
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id','marketplace_product_id')->with('product_store')->select(['marketplace_product_id','marketplace_store_id','title','slug','description']);
    }
    
}

