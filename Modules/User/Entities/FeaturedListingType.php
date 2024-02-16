<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class FeaturedListingType extends Model
{
    //protected $fillable = [];

    public function featuredListing()
    {
        return $this->hasMany(FeaturedListing::class);
    }

}
