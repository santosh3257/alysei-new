<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class Certificate extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
    public function photo_of_label()
    {
        return $this->belongsTo(Attachment::class, 'photo_of_label','id');
    }
    
    public function fce_sid_certification()
    {
        return $this->belongsTo(Attachment::class, 'fce_sid_certification','id');
    }
    
    public function phytosanitary_certificate()
    {
        return $this->belongsTo(Attachment::class, 'phytosanitary_certificate','id');
    }
    
    public function packaging_for_usa()
    {
        return $this->belongsTo(Attachment::class, 'packaging_for_usa','id');
    }
    
    public function food_safety_plan()
    {
        return $this->belongsTo(Attachment::class, 'food_safety_plan','id');
    }
    
    public function animal_helath_asl_certificate()
    {
        return $this->belongsTo(Attachment::class, 'animal_helath_asl_certificate','id');
    }

    
}
