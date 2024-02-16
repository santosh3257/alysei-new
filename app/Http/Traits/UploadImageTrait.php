<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use Carbon\Carbon;
use App\Attachment;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;
use Illuminate\Support\Facades\Auth; 
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Validator;
use Storage;
use Image;
use League\Flysystem\Filesystem;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem\AwsS3v3\AwsS3Adapter;
//use App\Events\UserRegisterEvent;

trait UploadImageTrait
{
    
    /***
    Upload Image
    ***/
    public function uploadImage($img)
    {
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';

        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($img))
            {
                $headerImageName=$img->getClientOriginalName();
                $ext1=$img->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='uploads/'.$date.'/'.$newHeaderLogo;
                $img->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            
            
            $ext1 = $img->getClientOriginalExtension();
            $name = rand(1111,9999999).str_replace(' ','_',$img->getClientOriginalName());
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget,file_get_contents($img), $img->getClientOriginalExtension() === 'svg' ? ['mimetype' => 'image/svg+xml'] : []);

        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_type = $ext1;
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }
    //crop upload image function

    public function cropUploadImage($img)
    {
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';

        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($img))
            {
                $headerImageName=$img->getClientOriginalName();
                $ext1=$img->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='uploads/'.$date.'/'.$newHeaderLogo;
                $img->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            
            
            /* $ext1 = $img->getClientOriginalExtension();
            $name = rand(1111,9999999).str_replace(' ','_',$img->getClientOriginalName());
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget,file_get_contents($img)); */
            list($baseType, $image) = explode(';', $img);
            list(, $image) = explode(',', $image);
            $image = base64_decode($image);
            $imageName = rand(111111111, 999999999) . '.jpeg';
            $headerTarget = $target.''. $imageName;
            $url = Storage::disk('s3')->put($headerTarget, $image, 'public');

        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_type = 'jpg';
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }
    //end crop upload image function

    public function getAttachment($attachmentId){
       return Attachment::where('id',$attachmentId)->first();
    }

    public function getAttachmentBaseUrL($attachmentId){
       $certificate = Attachment::where('id',$attachmentId)->first();
       if(!empty($certificate))
       {
        return $certificate->base_url;
       }
       else
       {
        return "";
       }
    }

    public function getCertificatesById($attachmentId){
       $certificate = Attachment::where('id',$attachmentId)->first();
       if(!empty($certificate))
       {
        return $certificate->attachment_url;
       }
       else
       {
        return "";
       }
    }

    public function deleteAttachment($attachmentId){
        $attachment = Attachment::where('id',$attachmentId)->first();
        
        if($attachment){
            if(env('FILESYSTEM') == 'storage_file')
            {
                unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$attachment->attachment_url);
            }else{
                Storage::disk('s3')->delete($attachment->attachment_url);
            }

            Attachment::where('id',$attachmentId)->delete();
        }
        
    }

    /***
    Post Attchments
    ***/
    public function postAttchment($img)
    {
        //dd($img);
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';

        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($img))
            {
                $headerImageName=$img->getClientOriginalName();
                $ext1=$img->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $img->move($target,$newHeaderLogo);
                list($width, $height, $type, $attr) = getimagesize(env('APP_URL').''.$headerTarget);
                
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            $ext1 = $img->getClientOriginalExtension();
            $name = rand(1111,9999999).str_replace(' ','_',$img->getClientOriginalName());
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($img));
            list($width, $height, $type, $attr) = getimagesize($basePath.''.$headerTarget);
        }
        

        $activityAttachmentLink = new ActivityAttachmentLink;
        $activityAttachmentLink->attachment_url = $headerTarget;
        $activityAttachmentLink->attachment_type = $ext1;
        $activityAttachmentLink->height = $height;
        $activityAttachmentLink->width = $width;
        $activityAttachmentLink->base_url = $basePath;
        $activityAttachmentLink->save();
        
        return $activityAttachmentLink->activity_attachment_link_id;
    }
    /***
    Post Galleries
    ***/
    public function postGallery($img, $moduleId, $storeOrProduct,$count)
    {
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $headerTarget1 = null;
        $headerTarget2 = null;
        $headerTarget3 = null;
        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($img))
            {
                $headerImageName=$img->getClientOriginalName();
                $ext1=$img->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $img->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            $ext1 = $img->getClientOriginalExtension();
            $name = $count.md5(time()).'.'.$ext1;
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($img));

            // Thumbnail
            $thumbnail = Image::make($img)->resize(200, 200)->encode($ext1);
            $thumbnailName = $count.md5(time()).'-thumbnail.'.$ext1;
            $headerTarget1 = $target.$thumbnailName;
            Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

            // Medium
            $medium = Image::make($img)->resize(400, 400)->encode($ext1);
            $mediumName = $count.md5(time()).'-medium.'.$ext1;
            $headerTarget2 = $target.$mediumName;
            Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

            // Large
            $large = Image::make($img)->resize(684, 256)->encode($ext1);
            $largeName = $count.md5(time()).'-large.'.$ext1;
            $headerTarget3 = $target.$largeName;
            Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');

        }
        

        if($storeOrProduct == 1)
        {
            $activityAttachmentLink = new MarketplaceStoreGallery;
            $activityAttachmentLink->marketplace_store_id = $moduleId;    
        }
        elseif($storeOrProduct == 2)
        {
            $activityAttachmentLink = new MarketplaceProductGallery;
            $activityAttachmentLink->marketplace_product_id = $moduleId;
        }
        
        $activityAttachmentLink->attachment_url = $headerTarget;
        $activityAttachmentLink->attachment_thumbnail_url = $headerTarget1;
        $activityAttachmentLink->attachment_medium_url = $headerTarget2;
        $activityAttachmentLink->attachment_large_url = $headerTarget3;
        $activityAttachmentLink->attachment_type = $ext1;
        $activityAttachmentLink->base_url = $basePath;
        $activityAttachmentLink->save();

        /*if($storeOrProduct == 1)
            return $activityAttachmentLink->marketplace_store_gallery_id;
        elseif($storeOrProduct == 2)
            return $activityAttachmentLink->marketplace_product_gallery_id;*/
    }

    /** 
     * Create Post Image From Base64 string
     * 
     * Pamameters $img
     */ 
    public function createPostImage($img)
    {
        $date = date("Y/m");
        $year = date("Y");
        $month = date("m");

        $folderPath = "public/uploads/".$date."/";

        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = $folderPath . uniqid() . '. '.$image_type;

        if (!is_dir('public/uploads/' . $year)) {
          // dir doesn't exist, make it
          mkdir('public/uploads/' . $year);
        }
        if (!is_dir('public/uploads/' . $month)) {
          // dir doesn't exist, make it
          mkdir('public/uploads/' . $month);
        }

        file_put_contents($file, $image_base64);

        $activityAttachmentLink = new ActivityAttachmentLink;
        $activityAttachmentLink->attachment_url = $file;
        $activityAttachmentLink->attachment_url = $image_type;
        $activityAttachmentLink->save();
        
        return $activityAttachmentLink->activity_attachment_link_id;

    }

    /***
    Delete Post Attchments
    ***/
    public function deletePostAttachment($attachmentId){
        $attachment = ActivityAttachmentLink::where('activity_attachment_link_id',$attachmentId)->first();
        
        if($attachment){
            //unlink(env('APP_URL').''.$attachment->attachment_url);
            
            if(env('FILESYSTEM') == 'storage_file'){
                unlink('/home/ibyteworkshop/alyseiapi_ibyteworkshop_com/'.$attachment->attachment_url);
            }else{
                Storage::disk('s3')->delete($attachment->attachment_url);    
            }
            
            ActivityAttachmentLink::where('activity_attachment_link_id',$attachmentId)->delete();
        }
        
    }

    /****
    Upload Media using S3
    ****/
    public function uploadMediaUsingS3($img)
    {
        $status = [];
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        
        $date = date("Y/m");
        $target='uploads/'.$date;

        $folderPath = "uploads/".$date."/";
        
            $ext1 = $img->getClientOriginalExtension();
            $name = rand(1111,9999999).str_replace(' ','_',$img->getClientOriginalName());
            $filePath = $folderPath.''. $name;
            $url = Storage::disk('s3')->put($filePath, file_get_contents($img));

            $status = [$filePath, $ext1];
            return $status; 
        


        
        /*if(!empty($img))
        {
            $headerImageName=$img->getClientOriginalName();
            $ext1=$img->getClientOriginalExtension();
            $temp1=explode(".",$headerImageName);
            $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
            $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
            $img->move($target,$newHeaderLogo);
        }
        else
        {
            $headerTarget = '';
        }

        $status = [$headerTarget, $ext1];
        return $status; */

        
    }


    /***
    Upload Recipe Image
    ***/
    public function uploadRecipeImage($image)
    {
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $headerTarget1 = null;
        $headerTarget2 = null;
        $headerTarget3 = null;    
        $slug = time().'-recipe-img'; //name prefix
        $recipe = $this->getFileName($image, $slug);
        $headerTarget = $target.''. str_replace(' ','_',$recipe['name']);
        Storage::disk('s3')->put($headerTarget,  base64_decode($recipe['file']), 'public');
        $imageUrl = $basePath.$headerTarget;
        // // Thumbnail
        $thumbnail = Image::make($imageUrl)->resize(27, 27)->encode('png');
        $thumbnailName = md5(time()).'-thumbnail.png';
        $headerTarget1 = $target.$thumbnailName;
        Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

        // Medium
        $medium = Image::make($imageUrl)->resize(75, 75)->encode('png');
        $mediumName = md5(time()).'-medium.png';
        $headerTarget2 = $target.$mediumName;
        Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

        // Large
        $large = Image::make($imageUrl)->resize(160, 160)->encode('png');
        $largeName = md5(time()).'-large.png';
        $headerTarget3 = $target.$largeName;
        Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');

        
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_thumbnail_url = $headerTarget1;
        $attachment->attachment_medium_url = $headerTarget2;
        $attachment->attachment_large_url = $headerTarget3;
        $attachment->attachment_type = 'png';
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }

    private function getFileName($image, $namePrefix)
    {
        list($type, $file) = explode(';', $image);
        list(, $extension) = explode('/', $type);
        list(, $file) = explode(',', $file);
        $result['name'] = $namePrefix . '.' . $extension;
        $result['file'] = $file;
        return $result;
    }

    // Upload User Profile Photo
    public function uploadPhotoImage($user_id, $photo){
        $date = date("Y/m");
        $target='uploads/avatar/';
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $headerTarget1 = null;
        $headerTarget2 = null;
        $headerTarget3 = null;
        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($photo))
            {
                $headerImageName=$photo->getClientOriginalName();
                $ext1=$photo->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $photo->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            // $user = auth()->user();
            // if(!empty($user->avatar_id)){
            //     $getAttachment = Attachment::find($user->avatar_id);
            //     $oldImageName = $getAttachment->attachment_url;
            //     $this->deleteAttachment($user->avatar_id);
            //     // Full Image
            //     $ext1 = $photo->getClientOriginalExtension();
            //     $headerTarget = $oldImageName;
            //     $url = Storage::disk('s3')->put($headerTarget, file_get_contents($photo));

            //     // Thumbnail
            //     $thumbnail = Image::make($photo)->resize(90, 90)->encode($ext1);
            //     $thumbnailName = md5(time()) . '-thumbnail.' . $ext1;
            //     $headerTarget1 = $target . $thumbnailName;
            //     Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

            //     // Medium
            //     $medium = Image::make($photo)->resize(120, 120)->encode($ext1);
            //     $mediumName = md5(time()) . '-medium.' . $ext1;
            //     $headerTarget2 = $target . $mediumName;
            //     Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

            //     // Large
            //     $large = Image::make($photo)->resize(190, 190)->encode($ext1);
            //     $largeName = md5(time()) . '-large.' . $ext1;
            //     $headerTarget3 = $target . $largeName;
            //     Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');
            // }
            // else{
                // Full Image
                $ext1 = $photo->getClientOriginalExtension();
                $name = rand(1111, 9999999) . '.' . $ext1;
                $headerTarget = $target . '' . $name;
                $url = Storage::disk('s3')->put($headerTarget, file_get_contents($photo));

                // Thumbnail
                $thumbnail = Image::make($photo)->resize(90, 90)->encode($ext1);
                $thumbnailName = md5(time()) . '-thumbnail.' . $ext1;
                $headerTarget1 = $target . $thumbnailName;
                Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

                // Medium
                $medium = Image::make($photo)->resize(120, 120)->encode($ext1);
                $mediumName = md5(time()) . '-medium.' . $ext1;
                $headerTarget2 = $target . $mediumName;
                Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

                // Large
                $large = Image::make($photo)->resize(190, 190)->encode($ext1);
                $largeName = md5(time()) . '-large.' . $ext1;
                $headerTarget3 = $target . $largeName;
                Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');
            // }
        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_thumbnail_url = $headerTarget1;
        $attachment->attachment_medium_url = $headerTarget2;
        $attachment->attachment_large_url = $headerTarget3;
        $attachment->attachment_type = $ext1;
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }


    // Upload User Cover Image
    public function uploadCoverImage($cover){
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $target1='uploads/thumbnail/'.$date."/";
        $target2='uploads/medium/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $headerTarget1 = null;
        $headerTarget2 = null;
        $headerTarget3 = null;
        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($cover))
            {
                $headerImageName=$cover->getClientOriginalName();
                $ext1=$cover->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $cover->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            
            // Full Image
            $ext1 = $cover->getClientOriginalExtension();
            $name = rand(1111,9999999).'.'.$ext1;
            $fullImage = Image::make($cover)->resize(1728, 504)->encode($ext1);
            $headerTarget = $target.''. $name;
            //Storage::disk('s3')->put($headerTarget, file_get_contents($cover));
            Storage::disk('s3')->put($headerTarget, (string)$fullImage, 'public');

            // Thumbnail
            $thumbnail = Image::make($cover)->resize(292, 120)->encode($ext1);
            $thumbnailName = md5(time()).'-thumbnail.'.$ext1;
            $headerTarget1 = $target.$thumbnailName;
            Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

            // Medium
            $medium = Image::make($cover)->resize(1224, 357)->encode($ext1);
            $mediumName = md5(time()).'-medium.'.$ext1;
            $headerTarget2 = $target.$mediumName;
            Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

            // Large
            $large = Image::make($cover)->resize(1728, 504)->encode($ext1);
            $largeName = md5(time()).'-large.'.$ext1;
            $headerTarget3 = $target.$largeName;
            Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');

        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_thumbnail_url = $headerTarget1;
        $attachment->attachment_medium_url = $headerTarget2;
        $attachment->attachment_large_url = $headerTarget3;
        $attachment->attachment_type = $ext1;
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }

    // Upload Featured Products, Award, Marketplace, all other front images
    public function uploadFrontImage($image){
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $target1='uploads/thumbnail/'.$date."/";
        $target2='uploads/medium/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $headerTarget1 = null;
        $headerTarget2 = null;
        $headerTarget3 = null;
        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($image))
            {
                $headerImageName=$image->getClientOriginalName();
                $ext1=$image->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $image->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            
            // Full Image
            $ext1 = $image->getClientOriginalExtension();
            $name = rand(1111,9999999).'.'.$ext1;
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($image));

            // Thumbnail
            $thumbnail = Image::make($image)->resize(200, 200)->encode($ext1);
            $thumbnailName = md5(time()).'-thumbnail.'.$ext1;
            $headerTarget1 = $target.$thumbnailName;
            Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

            // Medium
            $medium = Image::make($image)->resize(400, 400)->encode($ext1);
            $mediumName = md5(time()).'-medium.'.$ext1;
            $headerTarget2 = $target.$mediumName;
            Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

            // Large
            $large = Image::make($image)->resize(684, 256)->encode($ext1);
            $largeName = md5(time()).'-large.'.$ext1;
            $headerTarget3 = $target.$largeName;
            Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');

        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_thumbnail_url = $headerTarget1;
        $attachment->attachment_medium_url = $headerTarget2;
        $attachment->attachment_large_url = $headerTarget3;
        $attachment->attachment_type = $ext1;
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }


    // Upload walkthrough image front images
    public function uploadWalkthroughImage($image){
        $date = date("Y/m");
        $target='uploads/'.$date."/";
        $target1='uploads/thumbnail/'.$date."/";
        $target2='uploads/medium/'.$date."/";
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';

        $basePath = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/';
        $headerTarget1 = null;
        $headerTarget2 = null;
        $headerTarget3 = null;
        if(env('FILESYSTEM') == 'storage_file')
        {
            if(!empty($image))
            {
                $headerImageName=$image->getClientOriginalName();
                $ext1=$image->getClientOriginalExtension();
                $temp1=explode(".",$headerImageName);
                $newHeaderLogo=rand()."".round(microtime(true)).".".end($temp1);
                $headerTarget='public/uploads/'.$date.'/'.$newHeaderLogo;
                $image->move($target,$newHeaderLogo);
            }
            else
            {
                $headerTarget = '';
            }
        }
        else
        {
            $status = [];
            
            // Full Image
            $ext1 = $image->getClientOriginalExtension();
            $name = rand(1111,9999999).str_replace(' ','_',$image->getClientOriginalName());
            $headerTarget = $target.''. $name;
            $url = Storage::disk('s3')->put($headerTarget, file_get_contents($image));

            // Thumbnail
            $thumbnail = Image::make($image)->resize(200, 250)->encode($ext1);
            $thumbnailName = md5(time()).'-thumbnail.'.$ext1;
            $headerTarget1 = $target.$thumbnailName;
            Storage::disk('s3')->put($headerTarget1, (string)$thumbnail, 'public');

            // Medium
            $medium = Image::make($image)->resize(400, 450)->encode($ext1);
            $mediumName = md5(time()).'-medium.'.$ext1;
            $headerTarget2 = $target.$mediumName;
            Storage::disk('s3')->put($headerTarget2, (string)$medium, 'public');

            // Large
            $large = Image::make($image)->resize(600, 480)->encode($ext1);
            $largeName = md5(time()).'-large.'.$ext1;
            $headerTarget3 = $target.$largeName;
            Storage::disk('s3')->put($headerTarget3, (string)$large, 'public');

        }
        $attachment = new Attachment;
        $attachment->attachment_url = $headerTarget;
        $attachment->attachment_thumbnail_url = $headerTarget1;
        $attachment->attachment_medium_url = $headerTarget2;
        $attachment->attachment_large_url = $headerTarget3;
        $attachment->attachment_type = $ext1;
        $attachment->base_url = $basePath;
        $attachment->save();

        return $attachment->id;
    }

    public function uploadOrderInvoicePDFS3($pdf)
    {
        $status = [];
        $baseUrl = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
        
        $date = date("Y/m");
        $folderPath = "uploads/pdf/".$date."/";
        $filePath = $folderPath.time().'.pdf';
        //Storage::disk('s3')->put($filePath, file_get_contents($pdf->output()));
        Storage::disk('s3')->put($filePath, $pdf->output(), 'public');
        //$url = Storage::disk('s3')->put($filePath, file_get_contents($pdf));
        return $baseUrl.$filePath;
        
    }

}