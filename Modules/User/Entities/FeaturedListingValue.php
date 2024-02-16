<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class FeaturedListingValue extends Model
{
    protected $fillable = ["featured_listing_value_id","user_id","featured_listing_field_id","featured_listing_id","value","created_at","updated_at"];
    protected $primaryKey = 'featured_listing_values';
}
