<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class ConnectionRequestHubs extends Model
{
    protected $table = 'send_connection_request_hubs';
    protected $primaryKey = 'id';
}
