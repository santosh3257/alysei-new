<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Modules\User\Entities\State;
use Modules\User\Entities\Country;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\Role;
use App\Attachment;

class User extends Authenticatable
{
    use SoftDeletes;
	use Notifiable, HasApiTokens;
	
	protected $primaryKey = 'user_id';
    protected $table = 'users';

    protected $fillable = ['email','password','first_name','last_name','name','otp','otp_expired','role_id',"timezone","locale","account_enabled","vat_no","company_name","restaurant_name","country_id","state","phone","country_code","lattitude","longitude","address","notification_status"];

    public function roles(){
        return $this->belongsTo(Role::class, 'role_id','role_id')->select(array('role_id', 'name', 'slug', 'display_name'));
    }

    public function avatar_id()
    {
        return $this->belongsTo(Attachment::class, 'avatar_id','id');
    }
    public function profile_img()
    {
        return $this->belongsTo(Attachment::class, 'avatar_id','id');
    }
    public function cover_id()
    {
        return $this->belongsTo(Attachment::class, 'cover_id','id');
    }

    public function state() 
    {
        return $this->belongsTo(State::class, 'state','id');
    }

    public function state_data()
    {
        return $this->belongsTo(State::class, 'state','id')->select(['id', 'name']);
    }

    public function userhubs(){
        return $this->hasMany(UserSelectedHub::class, 'user_id','user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id','id')->select(['id', 'name','phonecode']);
    }
}
