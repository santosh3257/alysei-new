<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Notification;
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use Modules\User\Entities\UserFieldValue;
use Modules\User\Entities\UserFieldOption;
use Modules\User\Entities\UserField;
use Modules\User\Entities\Country;
use Modules\User\Entities\State;
use Modules\User\Entities\City;
use DB;
use Kreait\Firebase\Factory;
use Modules\User\Entities\Role;
use Modules\User\Entities\UserSelectedHub;
use Illuminate\Http\Response;
use Modules\User\Entities\Hub;
use Modules\User\Entities\ReportUser;
use Modules\Marketplace\Entities\MarketplaceStore;

class UserController extends CoreController
{
    use NotificationTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    public function conn_firbase(){
        
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        
        $database = $factory->createDatabase();    
        return $database;
    }

    public function addUserOrUpdateInFirebase($user_id, $name, $reviewStatus)
    {
        try{
            $data = $this->conn_firbase()->getReference('users/'.$user_id)
            ->update([
            'alysei_approval' => $reviewStatus,
            'notification' => 0,
            'url' => ''
            ]);
            return $data;
        }catch(\Exception $e){
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
                }
    }
    // Update user notification 
    public function updateUserNotificationCountFirebase($id)
    {
        try{
            $reference = $this->conn_firbase()->getReference('users');
            $snapshot = $reference->getChild($id);
            $getKey = $snapshot->getValue();
            if(isset($getKey['notification'])){
                $countNotification = $getKey['notification'];

                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => $countNotification+1
                ]);

                return $countNotification+1;
            }
            else{
                $data = $this->conn_firbase()->getReference('users/'.$id)
                ->update([
                'notification' => 0
                ]);

                return 0;
            }
        }catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }

    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /***
    dashboard
    ***/
    public function dashboard(Request $request)
    {
        return view('admin.home');
    }

    
    /***
    logout
    ***/
    public function logout(Request $request)
    {
        Auth::logout();
        return Redirect('login');
    }

    /***
    user list method
    ***/
    public function list(Request $request)
    {  
        $keyword = isset($_GET['keyword'])? $_GET['keyword'] : '';
        $query = User::where('role_id','!=',1);
        if((isset($_GET['keyword'])) && (!empty($_GET['keyword']))){
            $query->Where(function ($q) use ($keyword) {
            $q->where('email', 'LIKE', '%' . $keyword . '%')
                ->where('email', 'LIKE', '%' . $keyword . '%')
                ->orWhere('first_name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('company_name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('restaurant_name', 'LIKE', '%' . $keyword . '%');
            });
        }
        if((isset($_GET['role'])) && (!empty($_GET['role'])) && $_GET['role'] > -1){
            $query->where('role_id',$_GET['role']);
        }
        $users = $query->orderBy('user_id', 'DESC')->paginate(25);

        $roles = Role::where('slug','!=','admin')->where('slug','!=','super_admin')->get();
       
        return view('user::admin.user.list', compact('users','roles'));
    }
    
    
    public function userDelete($id) 
    {
        $user = User::where('user_id', $id)->first();
        if($user){
            $updatedEmail = $user->email."-deleted-".time();
            User::where(['user_id'=>$user->user_id])->update(['email' => $updatedEmail]);
            $user->delete();
            DB::table('events')->where('user_id', $id)->delete();
            DB::table('blogs')->where('user_id', $id)->delete();
            DB::table('trips')->where('user_id', $id)->delete();
            DB::table('marketplace_stores')->where('user_id', $id)->delete();
            DB::table('recipes')->where('user_id', $id)->delete();
            DB::table('awards')->where('user_id', $id)->delete();
            DB::table('featured_listings')->where('user_id', $id)->delete();
            DB::table('marketplace_products')->where('user_id', $id)->delete();
            DB::table('certificates')->where('user_id', $id)->delete();
            DB::table('connections')->Where(function ($q) use ($id) {
                $q->where('user_id', $id)
                  ->orWhere('resource_id', $id);
            })->delete();
            DB::table('followers')->Where(function ($q) use ($id) {
                $q->where('user_id', $id)
                  ->orWhere('follow_user_id', $id);
            })->delete();
            DB::table('user_selected_hubs')->where('user_id', $id)->delete();
            DB::table('device_tokens')->where('user_id', $id)->delete();
            DB::table('featured_listing_values')->where('user_id', $id)->delete();
            DB::table('event_likes')->where('user_id', $id)->delete();
            DB::table('temp_hubs')->where('user_id', $id)->delete();
            DB::table('preference_map_users')->where('user_id', $id)->delete();
            DB::table('recipe_review_ratings')->where('user_id', $id)->delete();
            DB::table('recipe_favourites')->where('user_id', $id)->delete();
            DB::table('marketplace_favourites')->where('user_id', $id)->delete();
            DB::table('marketplace_review_ratings')->where('user_id', $id)->delete();
            DB::table('marketplace_recent_search')->where('user_id', $id)->delete();
            DB::table('marketplace_product_enqueries')->where('user_id', $id)->delete();
            DB::table('activity_actions')->where('subject_id', $id)->where('subject_type','user')->delete();
            DB::table('core_comments')->where('resource_id', $id)->where('poster_type','user')->delete();
            DB::table('activity_likes')->where('resource_id', $id)->where('poster_type','user')->delete();
            DB::table('block_lists')->where('block_user_id', $id)->delete();
            DB::table('core_comment_likes')->where('poster_id', $id)->delete();
            $message = "User deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
        
    }

    /***
    update user status
    ***/
    public function userStatus(Request $request)
    {
        $sql= User::whereIn('user_id',$request->id)
        ->update(['account_enabled' => $request->status]);
             
        return $sql;
    }

    /***
    user edit method
    ***/
    public function edit(Request $request, $id)
    { 
        $user = User::with('country','state')->where('user_id',$id)->first();
        $user_id = $user->user_id;
        $fields = $this->getUserSelectedFields($user->role_id,$id);

        $contact = User::select('user_id','role_id','email','country_code','phone','address','website','fb_link','about')->where('user_id', $id)->first();
        $userPrivacy = User::select('user_id','allow_message_from','who_can_view_age','who_can_view_profile','who_can_connect')->where('user_id', $id)->first();

            $userEmailPreference = User::select('user_id','private_messages','when_someone_request_to_follow','weekly_updates')->where('user_id', $id)->first();
            
            
               
        $privacyData = ['user_id' => $userPrivacy->user_id, 'allow_message_from' => $userPrivacy->allow_message_from, 'who_can_view_age' => $userPrivacy->who_can_view_age, 'who_can_view_profile' => $userPrivacy->who_can_view_profile, 'who_can_connect' => $userPrivacy->who_can_connect];

        $messagePreference = ['user_id' => $id, 'private_messages' => $userEmailPreference->private_messages, 'when_someone_request_to_follow' => $userEmailPreference->when_someone_request_to_follow, 'weekly_updates' => $userEmailPreference->weekly_updates];
        if($user->role_id != 10)
        {
            $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin','Importer_and_Distributer','voyagers'])->orderBy('order')->get();
        }
        else
        {
            $roles = Role::select('role_id','name','slug')->where('slug','voyagers')->get();
        }
        

    
        foreach ($roles as $key => $role) {
            $roles[$key]->name = $this->translate('messages.'.$roles[$key]->name,$roles[$key]->name);
        }

        $selectedhubs = UserSelectedHub::with('hub')->where('user_id',$id)->get();
        $hubs = DB::select("select * from hubs where id not in (select hub_id from user_selected_hubs where user_id=$id)");

        $roleFields = DB::table('user_field_map_roles')
                                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                                      ->where("role_id","=",$user->role_id)
                                      ->where("require_update","=",'true')
                                      ->where("conditional","=",'no')
                                      ->orderBy("edit_profile_field_order","asc")
                                      ->get();

        
        if(count($roleFields)>0){
            foreach ($roleFields as $key => $value) {
                if($value->title == 'Product type'){
                    $data = [];
                    $value->options = $this->getUserFieldOptionParent($value->user_field_id);
                        if(!empty($value->options)){

                            foreach ($value->options as $k => $oneDepth) {

                                    $fieldValuessParents = DB::table('user_field_values')
                                        ->where('user_id', $user_id)
                                        ->where('user_field_id', $oneDepth->user_field_id)
                                        ->where('value', $oneDepth->user_field_option_id)
                                        ->first();

                                    if(!empty($fieldValuessParents) )
                                    {
                                        $value->options[$k]->is_selected = true;

                                    }else{

                                        if($value->options[$k]->option == 'No'){
                                            $value->options[$k]->is_selected = true;
                                        }else{
                                            $value->options[$k]->is_selected = false;
                                        }
                                    }

                                    $value->options[$k]->option = $oneDepth->option;

                                    //Check Option has any Field Id
                                    $checkRow = DB::table('user_field_maps')->where('user_field_id','=',$value->user_field_id)->where('role_id','=',$user->role_id)->first();

                                    if($checkRow){
                                        $value->parentId = $checkRow->option_id;
                                    }
                                        $fieldValuesParent = DB::table('user_field_values')
                                        ->where('user_id', $user_id)
                                        ->where('user_field_id', $value->user_field_id)
                                        ->get();

                                        $userFieldValuesParent = $fieldValuesParent->pluck('value')->toArray();

                                    
                                    $data = $this->getUserFieldOptionsNoneParent($value->user_field_id,$oneDepth->user_field_option_id,$userFieldValuesParent);

                                    $value->options[$k]->options = $data;

                                    
                                    foreach ($value->options[$k]->options as $optionKey => $optionValue) {

                                        $fieldValues = DB::table('user_field_values')
                                        ->where('user_id', $user_id)
                                        ->where('user_field_id', $optionValue->user_field_id)
                                        ->get();

                                        $userFieldValues = $fieldValues->pluck('value')->toArray();


                                        $options = $this->getUserFieldOptionsNoneParent($optionValue->user_field_id, $optionValue->user_field_option_id, $userFieldValues);

                                        $value->options[$k]->options[$optionKey]->options = $options;
                                        
                                    }  
                                    

                            }
                        }
                }        
            }
        }
        // dd($roleFields);
        // die();
        return view('user::admin.user.edit', compact('user','fields','contact','roles','privacyData','messagePreference','selectedhubs','hubs','roleFields'));
    }

    /*** 
    Admin update product types
    ***/
    public function adminUpdateProductType(Request $request){
        //return $request->selectedId;
        try{
            $selectedProductTypes = $request->selectedId;
            if(count($selectedProductTypes) > 0){
                $selectedProductType = UserFieldValue::where('user_id',$request->id)->where('user_field_id',2)->delete();
                if($selectedProductType){
                    foreach($selectedProductTypes as $key=>$pType){
                        $fieldValue = new UserFieldValue();
                        $fieldValue->user_id = $request->id;
                        $fieldValue->user_field_id = 2;
                        $fieldValue->value = $pType;
                        $fieldValue->save();
                    }
                }
                $message = "Product type has been changed successfully";
                return response()->json(array('success' => true, 'message' => $message));
            }
            else{
                $message = "We can't be deleted";
                return response()->json(array('success' => false, 'message' => $message));
            }
        }
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
    }

    /***
    update alysei progress status
    ***/
    public function updateProgressStatus(Request $request, $userId = '')
    {
        $role = "";
        if($userId){

            $user = User::where('user_id',$userId)->first();
            if($user){
                $userName = $user->name;
                switch($user->role_id)
                {
                    case 3:
                        $role = "Producer";
                        break;

                    case 4:
                        $role = "Importer";
                        break;

                    case 5:
                        $role = "Distributer";
                        break;

                    case 6:
                        $role = "Importer & Distributer";
                        break;

                    case 7:
                        $role = "Voice Of Expert";
                        break;

                    case 8:
                        $role = "Travel Agency";
                        break;

                    case 9:
                        $role = "Restaurant";
                        break;

                    case 10:
                        $role = "Voyager";
                        break;

                    default:
                        $role = "Importer";
                        break;
                }       
            }
             
        }
        

        if($request->progress_level == 'alysei_review')
        {
            $title = "Your request has been approved. In order to become a Certified Alysei Member and access Alysei, please complete your profile.";
            $title_it = "La tua richiesta è stata approvata. Per diventare un Membro Certificato e accedere alla piattaforma Alysei, completa il tuo profilo.";
            $user = User::where('user_id', $userId)->update(['alysei_review' => '1']);
            $this->addUserOrUpdateInFirebase($userId, $userName, true);
        }
        elseif($request->progress_level == 'alysei_certification')
        {
            /* dd($role); */
            
            if($user->profile_percentage === 100){
                $title = "Congratulations! You are now an Alysei Certified ".$role;
                $title_it = "Congratulazioni! Ora sei ".$role;
                //$title = "Your request has been approved. In order to become a Certified Alysei Member and access Alysei, please complete your profile.";
                $user = User::where('user_id', $userId)->update(['alysei_certification' => '1']);
            }
            else{
                return redirect('dashboard/users/edit/'.$userId)->with('error','User profile incomplete');
            }
        }
        elseif($request->progress_level == 'alysei_recognition')
        {
            if($user->profile_percentage === 100){
                $title = "You have been recognized by Alysei";
                $title_it = "Sei stato riconosciuto da Alysei";
                $user = User::where('user_id', $userId)->update(['alysei_recognition' => '1']);
            }
            else{
                return redirect('dashboard/users/edit/'.$userId)->with('error','User profile incomplete');
            }
        }
        elseif($request->progress_level == 'alysei_qualitymark')
        {
            if($user->profile_percentage === 100){
                $title = "Your profile has been marked as qualitymark";
                $title_it = "Your profile has been marked as qualitymark";
                $user = User::where('user_id', $userId)->update(['alysei_qualitymark' => '1']);
            }
            else{
                return redirect('dashboard/users/edit/'.$userId)->with('error','User profile incomplete');
            }
            
        }
        elseif($request->progress_level == 'level_empty')
        {
            return redirect('dashboard/users/edit/'.$userId)->with('success','All steps has been completed');
        }

        $admin = User::where('role_id', '1')->first();

        $notificationCount = Notification::where('to',$userId)->where('title_en',$title)->count();

        if($notificationCount == 0){
            $saveNotification = new Notification;
            $saveNotification->from = $admin->user_id;
            $saveNotification->to = $userId;
            $saveNotification->notification_type = '9';
            $saveNotification->title_it = $title_it;
            $saveNotification->title_en = $title;
            $saveNotification->redirect_to = 'membership_progress';
            $saveNotification->redirect_to_id = 0;
            //$saveNotification->sender_name = "Admin";
            $saveNotification->sender_name = "";
            $saveNotification->save();
        }
        
        $tokens = DeviceToken::where('user_id', $userId)->get();
        if(count($tokens) > 0)
        {

            $collectedTokenArray = $tokens->pluck('device_token');

            $selectedLocale = $this->pushNotificationUserSelectedLanguage($userId);
            if($selectedLocale == 'en'){
                $title = $title;
            }
            else{
                $title = $title_it;
            }
            $notificationCount = $this->updateUserNotificationCountFirebase($userId);
            $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null,"Welcome to Alysei!");

            $this->sendNotificationToIOS($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,null,null,null,null,null,null,null,null,null,null,"Welcome to Alysei!",null,null,$notificationCount);

            
        }
        
        return redirect('dashboard/users/edit/'.$userId)->with('success','Updated successfully');
    }

    /***
    Alysei Review Status
    ***/
    public function reviewStatus(Request $request)
    {
        //echo $request->status; die;
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_review' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

    /***
    Alysei Certification Status
    ***/
    public function certifiedStatus(Request $request)
    {
        
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_certification' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

    /***
    Alysei Recognised Status
    ***/
    public function recognisedStatus(Request $request)
    {
        //echo $request->status; die;
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_recognition' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

    /***
    Alysei Quality Marked Status
    ***/
    public function qmStatus(Request $request)
    {
        //echo $request->status; die;
        if($request->isMethod('post')){
            $user = User::where('user_id', $request->id)->update(['alysei_qualitymark' => $request->status]);
            /*$user->alysei_review = $request->status;
            $user->save();*/
            return 1;
        }
    }

   public function getUserSelectedFields($roleId, $userId){

    $roleFields = DB::table('user_field_map_roles')->select(['user_field_map_role_id','user_field_map_roles.user_field_id','role_id','title','placeholder','type'])
                      ->join('user_fields', 'user_fields.user_field_id', '=', 'user_field_map_roles.user_field_id')
                      ->where("role_id","=",$roleId)
                      ->where("display_on_registration","=",'true')
                      ->where("conditional","=",'no')
                      ->whereNotIn("user_field_map_roles.user_field_id",[17,25])
                      ->orderBy("order","asc")
                      ->get();

    foreach($roleFields as $key => $roleField){
        $roleFields[$key]->answer =  DB::table("user_field_values")->select(['user_id','user_field_id','value','table_name'])->where(["user_id" => $userId,"user_field_id" => $roleField->user_field_id])->get();
    }

    foreach($roleFields as $key => $roleField){
        $roleFields[$key]->value = "";
        if(count($roleField->answer) == 1 && $roleField->answer[0]->table_name =='' && $roleField->type !== 'select' && $roleField->type !== 'multiselect' && $roleField->type !== 'radio'){
            
            $roleFields[$key]->value = $roleField->answer[0]->value;

        }if(count($roleField->answer) == 1 && $roleField->answer[0]->table_name =='' && ($roleField->user_field_id == 4 || $roleField->user_field_id == 5 || $roleField->user_field_id == 6)){
            
            $roleFields[$key]->value = $roleField->answer[0]->value;

        }elseif (count($roleField->answer) == 1 && $roleField->answer[0]->table_name) {
            $data = DB::table($roleField->answer[0]->table_name)
                                     ->where('id', $roleField->answer[0]->value)
                                     ->first();
            if($data)
                $roleFields[$key]->value = $data->name;
        }elseif ($roleField->type == 'map') {
            $productType = [];
            foreach($roleField->answer as $k => $answer){
                    if($answer->value)
                        $productType[] = $answer->value;
                }

            if(!empty($productType)){
                $roleFields[$key]->value = implode(',',$productType);
            }
        
        }elseif (count($roleField->answer) > 0 && $roleField->answer[0]->table_name =='') {

            $productType = [];
            $selectedValue = [];
            foreach($roleField->answer as $k => $answer){
                if($answer->value !== "" && $answer->value !== " " && $answer->value !== null){
                    
                    $productTypeOption = UserFieldOption::where('user_field_option_id',$answer->value)->select('option','parent')->first();
                    if($answer->user_field_id == 2){

                        if($productTypeOption->parent !== 0){
                            $selectedParent = UserFieldOption::where('user_field_option_id',$productTypeOption->parent)->select('option','parent')->first();
                            $topParent = UserFieldOption::where('user_field_option_id',$selectedParent->parent)->select('option','user_field_id')->first();
                            $productType[$topParent->option][$selectedParent->option][] = $productTypeOption->option;
                        }

                    }else{
                        if($productTypeOption)
                            $selectedValue[] = $productTypeOption->option;    
                    }
                }
            }
            if(!empty($selectedValue)){
                $roleFields[$key]->value = implode(',',$selectedValue);
            }

            if(!empty($productType)){
                $roleFields[$key]->value = $productType;
            }
            
        }
    }

    return $roleFields;

   }

   public function deleteAllUsers(Request $request){
        try{
            $userId = $request->ids;
            if(!empty($userId)){
                foreach($userId as $id){
                    $user = User::where('user_id', $id)->first();
                    if($user){
                        $updatedEmail = $user->email."-deleted-".time();
                        User::where(['user_id'=>$user->user_id])->update(['email' => $updatedEmail]);
                        $user->delete();
                        DB::table('events')->where('user_id', $id)->delete();
                        DB::table('blogs')->where('user_id', $id)->delete();
                        DB::table('trips')->where('user_id', $id)->delete();
                        DB::table('marketplace_stores')->where('user_id', $id)->delete();
                        DB::table('recipes')->where('user_id', $id)->delete();
                        DB::table('awards')->where('user_id', $id)->delete();
                        DB::table('featured_listings')->where('user_id', $id)->delete();
                        DB::table('marketplace_products')->where('user_id', $id)->delete();
                        DB::table('certificates')->where('user_id', $id)->delete();
                        DB::table('connections')->Where(function ($q) use ($id) {
                            $q->where('user_id', $id)
                              ->orWhere('resource_id', $id);
                        })->delete();
                        DB::table('followers')->Where(function ($q) use ($id) {
                            $q->where('user_id', $id)
                              ->orWhere('follow_user_id', $id);
                        })->delete();
                        DB::table('user_selected_hubs')->where('user_id', $id)->delete();
                        DB::table('device_tokens')->where('user_id', $id)->delete();
                        DB::table('featured_listing_values')->where('user_id', $id)->delete();
                        DB::table('event_likes')->where('user_id', $id)->delete();
                        DB::table('temp_hubs')->where('user_id', $id)->delete();
                        DB::table('preference_map_users')->where('user_id', $id)->delete();
                        DB::table('recipe_review_ratings')->where('user_id', $id)->delete();
                        DB::table('recipe_favourites')->where('user_id', $id)->delete();
                        DB::table('marketplace_favourites')->where('user_id', $id)->delete();
                        DB::table('marketplace_review_ratings')->where('user_id', $id)->delete();
                        DB::table('marketplace_recent_search')->where('user_id', $id)->delete();
                        DB::table('marketplace_product_enqueries')->where('user_id', $id)->delete();
                        DB::table('activity_actions')->where('subject_id', $id)->where('subject_type','user')->delete();
                        DB::table('activity_spams')->where('report_by',$id)->delete();
                        DB::table('core_comments')->where('resource_id', $id)->where('poster_type','user')->delete();
                        DB::table('activity_likes')->where('resource_id', $id)->where('poster_type','user')->delete();
                        DB::table('block_lists')->where('block_user_id', $id)->delete();
                        DB::table('core_comment_likes')->where('poster_id', $id)->delete();
                    }
                }

                $message = "User deleted successfuly";
                return response()->json(array('success' => true, 'message' => $message));
            }
            else{
                 $message = "We can't be deleted";
                 return response()->json(array('success' => true, 'message' => $message));
            }
        }
        catch(\Exception $e){
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>$e->getMessage()],$this->exceptionStatus); 
        }
   }

   public function update(Request $request){
        $input = $request->all();
        if(!empty($input) && array_key_exists('role_id',$input)){

            $storeUserName = '';
            if($input['role_id'] == 7 || $input['role_id'] == 10){
                $data = []; 
                $data['first_name'] = $input['first_name'];
                $data['last_name'] = $input['last_name'];
                $data['name'] = $input['first_name'].' '.$input['last_name'];
                $storeUserName = $input['first_name'].'_'.$input['last_name'].'_'.rand(10,100);
                $firstNameFieldId = UserField::select('user_field_id')->where('name','first_name')->first();
                $lastNameFieldId = UserField::select('user_field_id')->where('name','last_name')->first();
                
                User::where('user_id',$input['user_id'])->update($data);
                UserFieldValue::where(['user_id' => $input['user_id'],
                                       'user_field_id' => $firstNameFieldId->user_field_id]
                                   )->update(['value' => $input['first_name']]);
                UserFieldValue::where(['user_id' => $input['user_id'],
                                       'user_field_id' => $lastNameFieldId->user_field_id]
                                   )->update(['value' => $input['last_name']]);

            }elseif($input['role_id'] == 3 || $input['role_id'] == 4 || $input['role_id'] == 5 || $input['role_id'] == 6 ||$input['role_id'] == 8){

                $data = [];
                $storeUserName = $input['company_name'];
                $data['company_name'] = $input['company_name'];
                $data['name'] = $input['company_name'].'_'.rand(10,100);
                User::where('user_id',$input['user_id'])->update($data);

                $field = UserField::select('user_field_id')->where('name','company_name')->first();

                UserFieldValue::where(['user_id' => $input['user_id'],
                                       'user_field_id' => $field->user_field_id]
                                   )->update(['value' => $input['company_name']]);
                

            }elseif($input['role_id'] == 9){

                $data = [];
                $storeUserName = $input['restaurant_name'];
                $data['restaurant_name'] = $input['restaurant_name'];
                $data['name'] = $input['restaurant_name'].'_'.rand(10,100);
                User::where('user_id',$input['user_id'])->update($data);

                $field = UserField::select('user_field_id')->where('name','restaurant_name')->first();

                UserFieldValue::where(['user_id' => $input['user_id'],
                                       'user_field_id' => $field->user_field_id]
                                   )->update(['value' => $input['restaurant_name']]);

            }

            $store = MarketplaceStore::where('user_id',$input['user_id'])->first();
            if($store){
                $data = [];
                $data['name'] = $storeUserName;
                MarketplaceStore::where('marketplace_store_id',$store->marketplace_store_id)->update($data);
            }

        }

        $message = "User updated successfuly";
        return redirect()->back()->with('success', $message);
   }

   public function removeUserHub(Request $request){
        $input = $request->all();
        if(!empty($input)){
            if(array_key_exists('hub_id',$input) && array_key_exists('user_id',$input)){
                UserSelectedHub::where(['hub_id' => $input['hub_id'],'user_id' => $input['user_id']])->delete();
                echo  "hub deleted successfuly";exit;
            }
        }
   }

   public function addUserHub(Request $request){
        $input = $request->all();
        if(!empty($input) && array_key_exists('hub_id',$input) && array_key_exists('user_id',$input)){
            foreach($input['hub_id'] as $hub){
                UserSelectedHub::create(['user_id'=>$input['user_id'],'hub_id' => $hub]);
            }

            $message = "Hub updated successfuly";
            return redirect()->back()->with('success', $message);
        }
   }

   public function getReportedUsers(){
        $reportUsers = ReportUser::with('report_by_user_info','report_to_user_info')->paginate(20);
        // echo '<pre>';
        // print_r($reportUsers);
        // echo '</pre>';
        return view('user::user_report.index', compact('reportUsers'));
   }

   /*
     * Get All Fields Option who are child
     * @params $user_field_id 
    */ 
    public function getUserFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->whereNull('deleted_at') 
                    ->orderBy('weight','ASC')
                    ->orderBy('option','ASC')
                    ->get();

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);
            }
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Get user field parent
     * @params $user_field_id 
    */
    public function getUserFieldOptionGrandParent($fieldId){
        $fieldOptionDataSuperParent = '';
        $fieldOptionData = [];

        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_option_id','=',$fieldId)
                    ->whereNull('deleted_at') 
                    ->orderBy('weight','ASC')
                    ->orderBy('option','ASC')
                    ->first();
        }

        if(!empty($fieldOptionData)){
            return $fieldOptionData->user_field_option_id;     
        }else{
            return '';
        }
        
        
        if(!empty($fieldOptionDataSuperParent)){
            return $fieldOptionDataSuperParent->user_field_option_id;    
        }else{
            return $fieldOptionDataSuperParent;
        }
        
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($fieldId, $parentId, $userFieldValues){

        $fieldOptionData = [];
        //echo $parentId;
        if($fieldId > 0 && $parentId > 0){
            $fieldOptionData = DB::table('user_field_options')
                ->where('user_field_id','=',$fieldId)
                ->where('parent','=',$parentId)
                ->whereNull('deleted_at') 
                ->orderBy('weight','ASC')
                ->orderBy('option','ASC')
                ->get();                                


            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->option = $this->translate('messages.'.$option->option,$option->option);

                if(in_array($option->user_field_option_id, $userFieldValues))
                {
                    //echo $parentId;
                    $fieldOptionData[$key]->is_selected = true;    
                }
                else
                {
                    $fieldOptionData[$key]->is_selected = false;
                }
                
            }
        }
        
        return $fieldOptionData;    
        
    }

}