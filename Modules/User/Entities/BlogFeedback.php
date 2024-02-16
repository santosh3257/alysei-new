<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogFeedback extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'blog_id';
    protected $table = 'blog_feedbacks';
}
