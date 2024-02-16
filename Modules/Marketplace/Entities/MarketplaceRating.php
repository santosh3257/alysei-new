<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\User;

class MarketplaceRating extends Model
{
    protected $table = 'marketplace_review_ratings';
    protected $primaryKey = 'marketplace_review_rating_id';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}

