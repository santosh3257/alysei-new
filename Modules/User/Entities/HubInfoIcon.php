<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class HubInfoIcon extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'hub_info_icons';
    protected $fillable = ['role_id','message_en','message_it','created_at','updated_at'];

    public function roledata()
    {
        return $this->belongsTo(Role::class, 'role_id','role_id');
    }
}
