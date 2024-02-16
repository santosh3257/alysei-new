<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\MarketplaceTaxClasses;
use Modules\Marketplace\Entities\MapClassTax;
use Modules\Marketplace\Entities\MarketplaceTax;
use Modules\Marketplace\Entities\MarketplaceProduct;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Support\Facades\DB;

class TaxClassesController extends Controller
{

    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    /**
     * Display a listing of the Tax Classes.
     * @return Response
     */
    public function getTaxClasses()
    {
        try
        {
            $user = $this->user;
            $taxeClasses = MarketplaceTaxClasses::with('getTaxClasses.getTaxDetail')->where('user_id',$user->user_id)->orderBy('created_at','desc')->paginate(20);
            if($taxeClasses){
                foreach($taxeClasses as $key=>$taxClass){
                    $productCount = MarketplaceProduct::where('class_tax_id',$taxClass->tax_class_id)->count();
                    $taxeClasses[$key]->product = $productCount;
                }
            }
            return response()->json(['success' => $this->successStatus,
                                    'data' => $taxeClasses,
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /**
     * Store a newly created tax classes in storage.
     * @param Request $request
     * @return Response
     */
    public function addTaxClasses(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'tax_id' => 'required|array',
                'tax_id.*' => 'integer',
                'name' => 'required',
            ]);
        

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $taxClass = new MarketplaceTaxClasses();
            $taxClass->user_id  = $user->user_id;
            $taxClass->name = $request->name;
            $taxClass->description = $request->description;
            $taxClass->save();

            // save tax in map table
            foreach($request->tax_id as $key=>$tax){
                $mapTax = new MapClassTax();
                $mapTax->class_id = $taxClass->tax_class_id;
                $mapTax->tax_id = $tax;
                $mapTax->save();
            }
            DB::commit();
            if($taxClass){
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Your class has been added successfully',
                                    ], $this->successStatus);
            }
            else{
                $message = 'Something went wrong try later';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /**
     * Show the form for editing the specified tax class.
     * @param int $id
     * @return Response
     */
    public function editMyTaxClass($tax_class_id)
    {
        $user = $this->user;
        $taxClass = MarketplaceTaxClasses::with('getTaxClasses')->where('tax_class_id',$tax_class_id)->where('user_id',$user->user_id)->first();
        if($taxClass){
            if(!empty($taxClass->getTaxClasses)){
                foreach($taxClass->getTaxClasses as $index=>$tax){
                    $taxInfo = MarketplaceTax::where('tax_id',$tax->tax_id)->first();
                    if($taxInfo){
                        $taxClass['getTaxClasses'][$index]->tax_name = $taxInfo->tax_name;
                        if($taxInfo->tax_type == 'percentage'){
                            $taxClass['getTaxClasses'][$index]->tax_with_price = $taxInfo->tax_name.' ('.$taxInfo->tax_rate.'%)';
                        }
                        else{
                            $taxClass['getTaxClasses'][$index]->tax_with_price = $taxInfo->tax_name.' ($'.$taxInfo->tax_rate.')';
                        }
                    }
                }
            }   
            
        }
        if($taxClass){
            return response()->json(['success' => $this->successStatus,
                                    'data' => $taxClass,
                                    'type' => 'add-tax-class'
                                    ], $this->successStatus);
        }
        else{
            $message = 'No record fount';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function updateMyTaxClass(Request $request, $tax_class_id)
    {
        try
        {
            $user = $this->user;
            $taxClass = MarketplaceTaxClasses::where('tax_class_id',$tax_class_id)->where('user_id',$user->user_id)->first();
            if($taxClass){
                if($request->has('tax_id')){
                    $validator = Validator::make($request->all(), [ 
                        'tax_id' => 'required|array',
                        'tax_id.*' => 'integer',
                        'name' => 'required',
                    ]);
                }
                else{
                    $validator = Validator::make($request->all(), [ 
                        'name' => 'required',
                    ]);
                }
                

                if ($validator->fails()) { 
                    return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
                }
            
                $taxClass->name = $request->name;
                $taxClass->description = $request->description;
                $taxClass->save();
                // save tax in map table
                if($request->has('tax_id')){
                    MapClassTax::where('class_id',$tax_class_id)->delete();
                    foreach($request->tax_id as $key=>$tax){
                        $mapTax =  new MapClassTax();
                        $mapTax->class_id = $taxClass->tax_class_id;
                        $mapTax->tax_id = $tax;
                        $mapTax->save();
                    }
                }
                
                return response()->json(['success' => true,
                                        'message' => 'Your tax class has been updated successfully',
                                        ], $this->successStatus);
            }
            else{
                $message = 'The tax id is not valid';
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function deleteMyTaxClass($tax_class_id)
    {
        $user = $this->user;
        $taxClass = MarketplaceTaxClasses::where('tax_class_id',$tax_class_id)->where('user_id',$user->user_id)->first();
        if($taxClass){
            $products = MarketplaceProduct::where('class_tax_id',$tax_class_id)->get();
            if(count($products) > 0){
                foreach($products as $key=>$product){
                    $updatedProduct = MarketplaceProduct::where('marketplace_product_id',$product->marketplace_product_id)->first();
                    if($updatedProduct){
                        $updatedProduct->class_tax_id = null;
                        $updatedProduct->save();
                    }
                }
            }
            $taxClass->delete();
            return response()->json(['success' => true,
                                    'message' => 'Tax class deleted successfuly',
                                    ], $this->successStatus);
        }
        else{
            $message = "We can't be deleted";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }
    }
}

