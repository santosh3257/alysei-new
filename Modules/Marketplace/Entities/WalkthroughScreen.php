<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;

class WalkthroughScreen extends Model
{
	protected $table = 'marketplace_walkthrough_screens';
    protected $primaryKey = 'marketplace_walkthrough_screen_id';
    protected $fillable = [];
}

