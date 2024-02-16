<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceProduct;
use App\Events\StoreReviewed;
use App\Notification;
use Modules\User\Entities\User;
use App\Http\Controllers\CoreController;
use Modules\Marketplace\Entities\Incoterms;
use Validator;
class StoreController extends CoreController
{
    public function index(){

        $stores = MarketplaceStore::orderBy('name','ASC')->paginate(10);
        return view('marketplace::stores.index',compact('stores'));
    }

    public function search(Request $request){
        
       $name = $request->name;
        $status = $request->status; 
        
        $q = MarketplaceStore::query();
        if (isset($name))
        {
            $q->when($name,function ($query,$name){
               $query->where('name','like','%'.$name.'%');
            });
        }

        if (isset($status) && $status > -1)
        {
            $q->where('status',$status);
        }
        

       $stores = $q->paginate(10);

       return view('marketplace::stores.index',compact('stores','name','status'));
    }

    public function view($id){
        $store = MarketplaceStore::with('logo','banner','firstProduct')->where('marketplace_store_id',$id)->first();
        // if($store->firstProduct){
        // dd($store->firstProduct);
        // die();
        // }
        return view('marketplace::stores.view',compact('store','id'));
    }

    public function changeStatusToApprove(Request $request, $id){
        try
        {   
            $store = MarketplaceStore::where('marketplace_store_id',$id)->first();
            if(!empty($store->first_product_id)){
                $updatedData['status'] = '1';
                
                MarketplaceStore::where('marketplace_store_id',$id)->update($updatedData);
                $updatedProduct['status'] = '1';
                MarketplaceProduct::where('marketplace_store_id',$id)->update($updatedProduct);

                //Send Email
                event(new StoreReviewed($id));

                //Save Notification
                $title = "Hi ".$store->name." Congratulations, your store has been reviewed from our team.";
                $title_it = "Hi ".$store->name." Congratulazioni, il tuo negozio è stato recensito dal nostro team.";

                $admin = User::where('role_id', '1')->first();
                $saveNotification = new Notification;
                $saveNotification->from = $admin->user_id;
                $saveNotification->to = $store->user_id;
                $saveNotification->notification_type = 'progress';
                $saveNotification->title_en = $title;
                $saveNotification->title_it = $title_it;
                $saveNotification->redirect_to = 'store';
                $saveNotification->redirect_to_id = $id;
                $saveNotification->save();
                $message = "Status has been changed";

                return redirect()->back()->with('success', $message); 
            }
            else{
                return redirect()->back()->with('error', "This store don't have products so we can't approved it. Thanks"); 
            }
            

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }

    public function updateStoreStatus(Request $request){

        try
        {  
            $storeId = $request->id;
            $status = $request->status;

            $store = MarketplaceStore::where('marketplace_store_id',$storeId)->first();
            $updatedData['status'] = $status;
            
            if($status == 1){
                if(empty($store->first_product_id)){
                    return response()->json(array('success' => false, 'message' => "This store don't have products so we can't approved it. Thanks"));
                }
                //Send Email
                event(new StoreReviewed($storeId));

                //Save Notification
                //$title = "Hi ".$store->name." Congratulations, your store is reviewed from our team.";
                $title = "Hi ".$store->name." Congratulations, your store has been reviewed from our team.";
                $title_it = "Hi ".$store->name." Congratulazioni, il tuo negozio è stato recensito dal nostro team.";
                $admin = User::where('role_id', '1')->first();
                $saveNotification = new Notification;
                $saveNotification->from = $admin->user_id;
                $saveNotification->to = $store->user_id;
                $saveNotification->notification_type = 'progress';
                $saveNotification->title_en = $title;
                $saveNotification->title_it = $title_it;
                $saveNotification->redirect_to = 'store';
                $saveNotification->redirect_to_id = $storeId;
                $saveNotification->save();
                $updatedProduct['status'] = '1';
                MarketplaceProduct::where('marketplace_store_id',$storeId)->update($updatedProduct);
            }
            else{
                $updatedProduct['status'] = '0';
                MarketplaceProduct::where('marketplace_store_id',$storeId)->update($updatedProduct);
            }

            MarketplaceStore::where('marketplace_store_id',$storeId)->update($updatedData);
            $message = "Status has been changed";

            return response()->json(array('success' => true, 'message' => $message));
            

        }catch(\Exception $e)
        {
            //dd($e->getMessage());
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $store = MarketplaceStore::where('marketplace_store_id', $id)->first();
        if($store){
            $store->delete();
            $message = "Store has been deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }

    // get All Inco Terms
    public function getIncoTerms(){
        $incoterms = Incoterms::orderBy('id','ASC')->paginate(10);
        return view('marketplace::incoterms.list',compact('incoterms'));
    }

    public function AddIncoTerms(){
        return view('marketplace::incoterms.create');
    }

    public function createIncoTerms(Request $request){
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'incoterms' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $incoterms = new Incoterms;
           $incoterms->incoterms = $request->incoterms;
           $incoterms->save();

            $message = "Incoterms added successfuly";
            return redirect()->back()->with('success', $message); 

        }
        catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }

    public function editIncoTerms($id){
        $incoterm = Incoterms::where('id',$id)->first();
        return view('marketplace::incoterms.edit', compact('incoterm'));
    }

    public function UpdateIncoTerms(Request $request, $id){
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'incoterms' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];

            $updatedData['incoterms'] = $request->incoterms;
            
            Incoterms::where('id',$id)->update($updatedData);

            $message = "IncoTerm updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
            
        }
    }
}
