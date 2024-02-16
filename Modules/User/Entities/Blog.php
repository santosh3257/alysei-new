<?php

namespace Modules\User\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Cviebrock\EloquentSluggable\Sluggable;

class Blog extends Model
{
    use SoftDeletes;
    use Sluggable;
    protected $primaryKey = 'blog_id';

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

}
