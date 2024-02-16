<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;

class Incoterms extends Model
{
    protected $table = 'incoterms';
    protected $primaryKey = 'id';
    protected $fillable = [];
}
