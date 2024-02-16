<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class MarketplaceBanner extends Model
{
	protected $primaryKey = 'marketplace_banner_id';
    protected $fillable = [];

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
