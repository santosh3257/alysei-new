<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;

class MarketplaceRecentSearch extends Model
{
    protected $table = 'marketplace_recent_search';
    protected $primaryKey = 'marketplace_recent_search_id';

    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }*/
}
