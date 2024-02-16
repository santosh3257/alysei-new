<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;
use Modules\Marketplace\Entities\MarketplaceRating;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Modules\Marketplace\Entities\MarketplaceFavourite;
use Modules\Marketplace\Entities\MarketplaceProductEnquery;
use Modules\User\Entities\UserFieldValue;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use Validator;
use DB;
use Cviebrock\EloquentSluggable\Services\SlugService;
use PDF;
use Storage;
use App\Http\Requests;
use App\Exports\MartketPlaceStats;

class StatsController extends CoreController
{
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public function downloadMarketPlaceAnalyst($filterType,$userId){
        try
        {
            if(!$userId){
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

            $productCount = MarketplaceProduct::leftJoin('marketplace_review_ratings', 'marketplace_products.marketplace_product_id', '=', 'marketplace_review_ratings.id')->select('marketplace_products.title','marketplace_products.product_category_id','marketplace_products.marketplace_product_id','marketplace_products.user_id',DB::raw('avg(marketplace_review_ratings.rating) as avg_rating'),DB::raw('count(marketplace_review_ratings.marketplace_review_rating_id) as rating_count'))->where('marketplace_products.user_id', $userId)->groupBy('marketplace_products.marketplace_product_id')->orderBy('avg_rating','desc')->get(); 

            if($productCount){
                foreach($productCount as $key=>$product){
                    $productCount[$key]->avg_rating = number_format((float)$product->avg_rating, 1, '.', '');
                    $productLikes = MarketplaceFavourite::where('id',$product->marketplace_product_id)->where('favourite_type','2')->count();
                    $productCount[$key]->total_reviews = $productLikes;

                    //Get Product Category
                    $options = DB::table('user_field_options')
                            ->where('head', 0)->where('parent', 0)
                            ->where('user_field_option_id', $product->product_category_id)
                            ->first();

                    $productCount[$key]->category_name = ($options->option) ?  $options->option : "";

                    //Get Prodct Enquiry
                    $importerUserNames = [];
                    $importerUserEmails = [];
                    $enqueries = MarketplaceProductEnquery::select(['user_id'])->where('product_id',$product->marketplace_product_id)->get();

                    if(!empty($enqueries)){
                        foreach($enqueries as $enquery){
                            $user = User::select(['name','email'])->where('user_id',$enquery->user_id)->first();
                            $importerUserNames[] = $user->name;
                            $importerUserEmails[] = $user->email;
                        }
                    }

                    $productCount[$key]->importer_names = implode(',', $importerUserNames);
                    $productCount[$key]->importer_emails = implode(',', $importerUserEmails);
                    $productCount[$key]->total_enquiries = count($enqueries);
                    
                }
            }
            
            $fileName = 'Stats.csv';
           
                $headers = array(
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$fileName",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                );

                $columns = array('Title', 'Category Name', 'Total Reviews', 'Avg  Rating', 'Total enqueries','Importer Name','Importer Emails');

                $callback = function() use($productCount, $columns) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($productCount as $task) {
                        fputcsv($file, array($task->title, $task->category_name, $task->total_reviews, $task->avg_rating, $task->total_enquiries,$task->importer_names,$task->importer_emails));
                    }

                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }
}