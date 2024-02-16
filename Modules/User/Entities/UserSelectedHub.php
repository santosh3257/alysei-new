<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;
use Illuminate\Notifications\Notifiable;
class UserSelectedHub extends Model
{

    use Notifiable;
	protected $table = "user_selected_hubs";

    protected $fillable = ['user_id','hub_id','created_at','updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function hub(){
        return $this->belongsTo(Hub::class,'hub_id','id');
    }

}
