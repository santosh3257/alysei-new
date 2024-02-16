<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\Role;
use Modules\User\Entities\FeaturedListing;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\User; 
use Validator;
use App\Attachment;
use DB;
use Cache;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\FeaturedListingValue;
use Modules\User\Entities\FeaturedListingType;
use Cviebrock\EloquentSluggable\Services\SlugService;

class FeaturedListingsController extends CoreController
{
    use UploadImageTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    /* 
     * User Settings
     */
    public function userSettings(){
        try{
                $userDetails = $this->user->only(['name', 'email','company_name','restaurant_name','locale','first_name','last_name','website']);

                $userFieldInfo = [];

                $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id);
                
                $products = [];
                
                foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                    
                    $featuredListing = FeaturedListing::with('image')
                                        ->where('user_id', $this->user->user_id)
                                        ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                        ->orderBy('featured_listing_id','DESC')->get(); 

                    $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
                    
                }

                //Get Featured Listing Fields

                //Get Featured Type
                $featuredTypes = $this->getFeaturedListingFieldsByRoleId($this->user->role_id);
                $fieldsData = [];
                foreach ($featuredTypes as $key => $value) {

                    $value->title = $this->translate('messages.'.$value->title,$value->title);

                    $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                    if(!empty($value->options)){
                        foreach ($value->options as $k => $oneDepth) {

                                $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                            }
                    }

                    $fieldsData[$value->featured_listing_type_slug][] = $value;
                }

                foreach($fieldsData as $fieldsDataKey => $fieldsDataValue){
                    

                    $key = array_search($fieldsDataKey, array_column($products, 'slug'));

                    $products[$key]['fields'] = $fieldsDataValue;
                }
                
                //END
                $data = ['user_settings'=>$userDetails,'products' => $products];
                return response()->json(['success' => $this->successStatus,
                                       'data' => $data                      
                                
                                ], $this->successStatus);

            }catch(\Exception $e){

                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
            }
    }

    /*
     * Insert Featured Listing
     * $request
     */
    public function postFeaturedListing(Request $request){
        try{
                $stateId='';
                $input = $request->all();
                $rules = [];
                $rules['featured_listing_type_id'] = 'required';
                $validator = Validator::make($input, $rules);

                if ($validator->fails()) { 
                    return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                }

                $featuredListingFields = $this->getFeaturedListingFields($this->user->role_id,$input['featured_listing_type_id']);

                if(count($featuredListingFields) == 0){
                    return response()->json(['success'=>$this->validationStatus,'errors' =>'Sorry,There are no fields for current role_id'], $this->validationStatus);
                }else{

                    $rules = $this->makeValidationRules($featuredListingFields,$input);
                    $inputData = $this->segregateInputData($input,$featuredListingFields);
                }

                
                if(!empty($rules) && !empty($inputData)){
                
                    $validator = Validator::make($inputData, $rules);

                    if ($validator->fails()) { 

                        return response()->json(['success'=>$this->validationStatus,'errors'=>$validator->errors()->first()], $this->validationStatus);
                    }

                    if(array_key_exists('title',$inputData) && 
                       //array_key_exists('listing_url',$inputData) && 
                       array_key_exists('featured_listing_type_id',$input)
                    ){

                        $featuredListingData = [];
                        $featuredListingData['title'] = strip_tags($inputData['title']);
                        $featuredListingData['slug'] = SlugService::createSlug(FeaturedListing::class, 'slug', $featuredListingData['title']);
                        if(isset($inputData['listing_url']))
                        {
                            $featuredListingData['listing_url'] = strip_tags($inputData['listing_url']);
                        }
                        
                        $featuredListingData['user_id'] = $this->user->user_id;
                        $featuredListingData['featured_listing_type_id'] = $input['featured_listing_type_id'];
                        //$featuredListingData['image_id'] = $this->uploadImage($inputData['image_id']);

                        if(!array_key_exists("featured_listing_id",$input)){
                            $update = false;
                            $featuredListing = FeaturedListing::create($featuredListingData);

                            $featuredListingId = $featuredListing->featured_listing_id;
                        }else{
                            $featuredListingId = $input["featured_listing_id"];
                            $update = true;
                            $featuredListing = FeaturedListing::where('featured_listing_id',$input['featured_listing_id'])->update($featuredListingData);    
                        }
                        //return $featuredListingData;

                        unset($input["featured_listing_type_id"]);
                        unset($input["featured_listing_id"]);
                        
                        foreach ($input as $key => $value) {
                            $data = [];
                            $create_new_data = [];
                            if($key == 1 && $update == false)
                            {
                                $value = $this->uploadFrontImage($value);
                                FeaturedListing::where('featured_listing_id', $featuredListingId)->update(['image_id' => $value]);
                            }

                            if($key == 1 && $update == true){
                                // $featuredListing = FeaturedListing::where('featured_listing_id', $featuredListingId)->first();
                                
                                // $this->deleteAttachment($featuredListing->image_id);

                                $value = $this->uploadFrontImage($value);
                                FeaturedListing::where('featured_listing_id', $featuredListingId)->update(['image_id' => $value]);

                            }

                            $data['featured_listing_id'] = $featuredListingId;
                            $data['featured_listing_field_id'] = $key;
                            $data['user_id'] = $this->user->user_id;
                            $data['value'] = $value;

                            if($update == false){
                               /*  if($data['value']){
                                    DB::table('featured_listing_values')->insert($data);
                                } */
                                DB::table('featured_listing_values')->insert($data);

                            }else{
                                        $find_listing = DB::table('featured_listing_values')->where('user_id',$this->user->user_id)->where('featured_listing_field_id',$key)->where('featured_listing_id',$featuredListingId)->first();
                                        if($find_listing){
                                            $query = DB::table('featured_listing_values')->where(['featured_listing_id'=>$featuredListingId,'featured_listing_field_id'=>$key,'user_id'=>$this->user->user_id])->update(['value'=>$value]);
                                        }
                                        else{
                                            $create_new_data['featured_listing_id'] = $featuredListingId;
                                            $create_new_data['featured_listing_field_id'] = $key;
                                            $create_new_data['user_id'] = $this->user->user_id;
                                            $create_new_data['value'] = $value;
                                            DB::table('featured_listing_values')->insert($create_new_data);
                                        }

                            }
                            
                        }


                        $mes = ($update == false) ? 'Successfully added' : 'Successfully updated';

                        $message = $this->translate('messages.'.$mes,$mes);

                        return response()->json(['success' => $this->successStatus,
                                        'message' => $message,
                                    ], $this->successStatus);
                    }

                }else{
                    return response()->json(['success'=>$this->validationStatus,'errors'=>"parameters are missing"], $this->validationStatus);
                }

                //dd($fields);

        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }    
    }

    /* 
     * get all featured listing
     * 
     */
    public function getAllFeaturedListing()
    {
        try
        {
            $fieldsTypes = $this->getFeaturedListingTypes($this->user->role_id);
            
            $products = [];

            foreach($fieldsTypes as $fieldsTypesKey => $fieldsTypesValue){
                
                $featuredListing = FeaturedListing::with('image')
                                    ->where('user_id', $this->user->user_id)
                                    ->where('featured_listing_type_id', $fieldsTypesValue->featured_listing_type_id)
                                    ->orderBy('featured_listing_id','DESC')->get(); 

                $products[] = ["title" => $fieldsTypesValue->title,"slug" => $fieldsTypesValue->slug,"products" => $featuredListing];
                
            }

            $data = ['products' => $products];


            if($data)
            {
                return response()->json(['success' => $this->successStatus,
                             'data' => $data,
                            ], $this->successStatus);
            }
            else
            {
                $message = "No listing found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
            }                 
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * delete featured listing
     * @params $request
     */
    public function deleteFeaturedListing(Request $request){
        try{
                $input = $request->all();

                $validator = Validator::make($input, [ 
                    'featured_listing_id' => 'required', 
                ]);

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
                
                $checkListing = FeaturedListing::where('user_id','=',$this->user->user_id)->where('featured_listing_id', $request->featured_listing_id)->first();
                if(!empty($checkListing))
                {
                    $isDeletedFeaturedListing = FeaturedListing::where('user_id','=',$this->user->user_id)->where('featured_listing_id', $request->featured_listing_id)->delete();
                    if($isDeletedFeaturedListing == 1)
                    {
                        $this->deleteAttachment($checkListing->image_id);
                        FeaturedListingValue::where('featured_listing_id', $request->featured_listing_id)->delete();
                        $message = "Featured listing deleted successfully";
                        return response()->json(['success' => $this->successStatus,
                                     'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
                    }
                    else
                    {
                        $message = "Listing does not exist";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                }
                                  
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /* 
     * Edit Featured Listing
     * params $featuredListingId
     */
    public function editFeaturedListing($featuredListingId){

        try{

            if($featuredListingId > 0){

                $featuredListing = FeaturedListing::where('featured_listing_id', $featuredListingId)->with('image')->first();

                if($featuredListing){

                    $featuredListingType = FeaturedListingType::where('featured_listing_type_id',$featuredListing->featured_listing_type_id)->first();

                    if(!$featuredListingType){
                        $errorMessage = $this->translate('messages.'."Featured listing type not available","Featured listing type not available");

                        return response()->json(['success'=>$this->validationStatus,'errors' =>$errorMessage], $this->validationStatus); 
                    }


                    //Get User details of current featured listing
                    $listingUserData = User::select('user_id','role_id')->where('user_id',$featuredListing->user_id)->first();


                    $title = "Edit ".$featuredListingType->title;
                    $title = $this->translate('messages.'.$title,$title);
                    
                    $fields = $this->getFeaturedListingFields($listingUserData->role_id,$featuredListing->featured_listing_type_id);    
                    $i=1;
                    $newValue = [];
                    foreach ($fields as $key => $value) {
                    
                        $value->title = $this->translate('messages.'.$value->title,$value->title);

                        $value->options = $this->getFeaturedListingFieldOptionParent($value->featured_listing_field_id);

                        if(!empty($value->options)){
                            foreach ($value->options as $k => $oneDepth) {

                                    $value->options[$k]->option = $this->translate('messages.'.$oneDepth->option,$oneDepth->option);
                                }
                        }

                        if($value->type == 'file'){
                            $value->value = ($featuredListing->image) ? $featuredListing->image->attachment_url : '';
                            $value->base_url = ($featuredListing->image) ? $featuredListing->image->base_url : '';
                        }else{

                            $featuredListingValue = FeaturedListingValue::where(['user_id' => $listingUserData->user_id,
                                                     'featured_listing_field_id' => $value->featured_listing_field_id,'featured_listing_id' => $featuredListing->featured_listing_id])->first();
                            if($featuredListingValue){

                                if($value->type == 'select'){

                                    foreach ($value->options as $optionKey => $optionValue) {

                                        if($optionValue->featured_listing_option_id == $featuredListingValue->value){
                                            $value->options[$optionKey]->is_selected = true;
                                        }else{
                                            $value->options[$optionKey]->is_selected = false;
                                        }
                                    }

                                }else{

                                    $value->value = $featuredListingValue->value;    
                                }
                                
                            }
                            else{

                                    $value->value = '';    
                                }

                        }

                        if($value->value == '' && ($featuredListing->user_id !== $this->user->user_id))
                        {
                            unset($fields[$key]);
                        }

                        if($i == 1)
                        {
                            $newValue = $fields[$key];
                        }
                            
                        $i++;
                    }
                    if(!empty($newValue))
                    {
                        array_shift($fields);
                        array_unshift($fields, $newValue);
                    }

                    return response()->json(['success'=>$this->successStatus,'data' => ["title" => $title,"fields" => $fields]], $this->successStatus); 

                }else{

                    $errorMessage = $this->translate('messages.'."Featured listing not available","Featured listing not available");

                    return response()->json(['success'=>$this->validationStatus,'errors' =>$errorMessage], $this->validationStatus); 
                }
                

            }else{

                $errorMessage = $this->translate('messages.'."Featured listing id is wrong","Featured listing id is wrong");


                return response()->json(['success'=>$this->validationStatus,'errors' =>$errorMessage], $this->validationStatus); 
            }    
        }catch(\Exception $e){

            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()], $this->exceptionStatus); 
        }
        

    }

    /*
     * Get Featured Listing Fields Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingFieldsByRoleId($roleId){
        
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->select("flt.title as featured_listing_type_title","flt.slug as featured_listing_type_slug","flfrm.*","fltrm.*","flf.*")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->join("featured_listing_field_role_maps as flfrm",function ($join) {
                $join->on('flfrm.featured_listing_type_id', '=', 'fltrm.featured_listing_type_id');
                $join->on('flfrm.featured_listing_type_id','=','flt.featured_listing_type_id');
            }) 

            ->join("featured_listing_fields as flf", 'flf.featured_listing_field_id', '=', 'flfrm.featured_listing_field_id')

            ->where("fltrm.role_id","=",$roleId)
            ->where("flfrm.role_id","=",$roleId)
            ->get();

        return $featuredTypes;
    }

    /*
     * Get All Fields Option who are child
     * @params $featured_listing_field_id 
    */
    public function getFeaturedListingFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('featured_listing_field_options')
                    ->where('featured_listing_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->get();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Get Featured Type Using Role Id
     * @params $roleId
     */
    public function getFeaturedListingTypes($roleId){
        $featuredTypes = DB::table("featured_listing_types as flt")
            ->join("featured_listing_type_role_maps as fltrm", 'fltrm.featured_listing_type_id', '=', 'flt.featured_listing_type_id')

            ->where("fltrm.role_id","=",$roleId)
            ->get();

        return $featuredTypes;
    }

    /*
     * Get Fields 
     * @params $roleId and $featuredListingTypeId
     */
    public function getFeaturedListingFields($roleId,$featuredListingTypeId){
        $featuredListingFields = DB::table("featured_listing_field_role_maps as flfrm")
            ->join("featured_listing_fields as flf", 'flf.featured_listing_field_id', '=', 'flfrm.featured_listing_field_id')

            ->where("flfrm.role_id","=",$roleId)
            ->where("flfrm.featured_listing_type_id","=",$featuredListingTypeId)
            ->get()->toArray();

        return $featuredListingFields;
    }

    /*
     * Make Validation Rules
     * @Params $featuredListingFields
     */

    public function makeValidationRules($featuredListingFields,$input){
        $rules = [];
        foreach ($featuredListingFields as $key => $field) {
            
            if($field->name == 'email' && $field->required == 'yes'){

                $rules[$field->name] = 'required|email|unique:users|max:50';

            }else if($field->name == 'password' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:8';

            }else if($field->name == 'first_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else if($field->name == 'last_name' && $field->required == 'yes'){

                $rules[$field->name] = 'required|min:3';

            }else if($field->type == 'file'){

                if(!array_key_exists("featured_listing_id",$input)){
                    $rules[$field->name] = 'required';
                }

            }else {

                if($field->required == 'yes'){
                    $rules[$field->name] = 'required|max:100';
                }
            }
        }

        return $rules;

    }

    /*
     * Segregate user input data
     * @Params $input and @featuredListingFields
     */
    public function segregateInputData($input,$featuredListingFields){
        $inputData = [];

        foreach($featuredListingFields as $key => $field){
            if(array_key_exists($field->featured_listing_field_id, $input)){
                $inputData[$field->name] = $input[$field->featured_listing_field_id];
            }
        }

        return $inputData;

    }
}
