<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class SiteLanguage extends Model
{
    protected $fillable = ['key','en','it','created_at','updated_at'];
}
