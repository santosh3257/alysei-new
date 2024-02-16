<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Marketplace\Entities\MarketplaceBrandLabel;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceStore;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;
use Cviebrock\EloquentSluggable\Sluggable;
use Modules\Marketplace\Entities\MarketplaceProductCategory;
use Modules\Marketplace\Entities\MarketplaceProductEnquery;
use Modules\Marketplace\Entities\MarketplaceTaxClasses;
use Modules\Marketplace\Entities\ProductOffer;
use Modules\User\Entities\UserFieldOption;

class MarketplaceProduct extends Model
{
    use SoftDeletes;
    use Sluggable;
    protected $primaryKey = 'marketplace_product_id';
    protected $fillable = [];
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    // Use Accessor
    public function getKeywordsAttribute($value)
    {
        return $value ?? '';
    }

    public function labels()
    {
        return $this->belongsTo(MarketplaceBrandLabel::class, 'brand_label_id','marketplace_brand_label_id');
    }

    public function product_gallery()
    {
        return $this->hasMany(MarketplaceProductGallery::class, 'marketplace_product_id','marketplace_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function store()
    {
        return $this->belongsTo(MarketplaceStore::class, 'marketplace_store_id','marketplace_store_id');
    }

    public function product_store()
    {
        return $this->belongsTo(MarketplaceStore::class, 'marketplace_store_id','marketplace_store_id')->select(['marketplace_store_id','name','slug','description']);
    }
    public function category()
    {
        return $this->belongsTo(MarketplaceProductCategory::class, 'product_category_id','marketplace_product_category_id')->select(['name']);
    }

    public function getProductInquiry(){
        return $this->belongsTo(MarketplaceProductEnquery::class, 'marketplace_product_id','product_id');
    }

    public function getProductTax(){
        return $this->belongsTo(MarketplaceTaxClasses::class, 'class_tax_id','tax_class_id');
    }
    public function getProductOffer()
    {
        return $this->belongsTo(ProductOffer::class, 'marketplace_product_id','product_id');
    }

    public function productCategory(){
        return $this->belongsTo(UserFieldOption::class, 'product_category_id','user_field_option_id')->select('user_field_option_id','option');
    }
}

