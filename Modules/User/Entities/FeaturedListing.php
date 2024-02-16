<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Cviebrock\EloquentSluggable\Sluggable;
class FeaturedListing extends Model
{
    use Sluggable;
	protected $fillable = ["title","slug","description","image_id","listing_url","featured_listing_type_id","created_at","updated_at","user_id"];
    protected $primaryKey = 'featured_listing_id';

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
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
