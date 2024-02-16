<?php

namespace Modules\User\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserFieldOption extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_field_id','option','created_at','optionType','updated_at','deleted_at','parent'];
    protected $table = 'user_field_options';


}