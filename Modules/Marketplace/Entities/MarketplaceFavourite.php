<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class MarketplaceFavourite extends Model
{
    protected $primaryKey = 'marketplace_favourite_id';
    protected $fillable = [];

    public function logo_id()
    {
        return $this->belongsTo(Attachment::class, 'logo_id','id');
    }
}

