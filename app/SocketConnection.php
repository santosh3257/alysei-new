<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocketConnection extends Model
{
	protected $primaryKey = 'socket_connection_id';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}
