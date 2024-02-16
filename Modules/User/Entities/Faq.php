<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'faq_id';
    protected $table = 'faq';
    protected $fillable = ['role_id','question_in_it','answer_in_it','question_in_en','answer_in_en','deleted_at','created_at','updated_at'];

    public function roledata()
    {
        return $this->belongsTo(Role::class, 'role_id','role_id');
    }
}
