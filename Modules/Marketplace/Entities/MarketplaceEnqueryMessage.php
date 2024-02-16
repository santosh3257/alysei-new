<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\User;
class MarketplaceEnqueryMessage extends Model
{
    protected $primaryKey = 'marketplace_enquery_message_id';
    protected $fillable = [];

    public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id','user_id')->with('profile_img')->select(['user_id', 'name','email','first_name','middle_name','last_name','company_name','restaurant_name','avatar_id']);
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id','user_id')->with('profile_img')->select(['user_id', 'name','email','first_name','middle_name','last_name','company_name','restaurant_name','avatar_id']);
    }
}
