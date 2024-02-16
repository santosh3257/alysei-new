<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\MarketplaceTax;
use Modules\Marketplace\Entities\MapClassTax;
use Illuminate\Support\Facades\Auth; 
use Validator;

class TaxController extends Controller
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
     * Display a listing of the Tax.
     * @return Response
     */
    public function getMyTaxes()
    {
        try
        {
            $user = $this->user;
            $taxes = MarketplaceTax::where('user_id',$user->user_id)->orderBy('created_at','desc')->paginate(20);
            if($taxes){
                foreach($taxes as $key=>$tax){
                    if($tax->tax_type == 'percentage'){
                        $taxes[$key]->tax_with_price = $tax->tax_name.' ('.$tax->tax_rate.'%)';
                    }
                    else{
                        $taxes[$key]->tax_with_price = $tax->tax_name.' ($'.$tax->tax_rate.')';
                    }
                }
            }
            return response()->json(['success' => $this->successStatus,
                                    'data' => $taxes,
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /**
     * Store a newly created Tax in storage.
     * @param Request $request
     * @return Response
     */
    public function AddMytax(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'tax_name' => 'required',
                'tax_rate' => 'required|numeric',
                'tax_type' => "required|in:percentage,fixed", 
            ]);
            

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            $tax = new MarketplaceTax();
            $tax->user_id  = $user->user_id;
            $tax->tax_name = $request->tax_name;
            $tax->tax_rate = $request->tax_rate;
            $tax->tax_type = $request->tax_type;
            $tax->save();

            if($tax){
                return response()->json(['success' => $this->successStatus,
                                    'message' => 'Your tax has been added successfully',
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
     * Show the form for editing the specified Tax.
     * @param int $id
     * @return Response
     */
    public function editMytax($tax_id)
    {
        $user = $this->user;
        $tax = MarketplaceTax::where('tax_id',$tax_id)->where('user_id',$user->user_id)->first();
       
        if($tax){
            return response()->json(['success' => $this->successStatus,
                                    'data' => $tax,
                                    'type' => 'add-tax',
                                    ], $this->successStatus);
        }
        else{
            $message = 'No record fount';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }
    }

    /**
     * Update the specified tax in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function updateMytax(Request $request, $tax_id)
    {
        $user = $this->user;
        $tax = MarketplaceTax::where('tax_id',$tax_id)->where('user_id',$user->user_id)->first();
        if($tax){
            $validator = Validator::make($request->all(), [ 
                'tax_name' => 'required',
                'tax_rate' => 'required|numeric',
                'tax_type' => "required|in:percentage,fixed", 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
           
            $tax->tax_name = $request->tax_name;
            $tax->tax_rate = $request->tax_rate;
            $tax->tax_type = $request->tax_type;
            $tax->save();
            
            return response()->json(['success' => $this->successStatus,
                                    'message' => 'Your tax has been updated successfully',
                                    ], $this->successStatus);
        }
        else{
            $message = 'The tax id is not valid';
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }
    }

    /**
     * Remove the specified Tax from storage.
     * @param int $id
     * @return Response
     */
    public function deleteMytax($tax_id)
    {
        $user = $this->user;
        $tax = MarketplaceTax::where('tax_id',$tax_id)->where('user_id',$user->user_id)->first();
        if($tax){
            $inTaxClass = MapClassTax::where('tax_id',$tax_id)->delete();
            $tax->delete();
            return response()->json(['success' => $this->successStatus,
                                    'message' => 'Tax rate deleted successfuly',
                                    ], $this->successStatus);
        }
        else{
            $message = "We can't be deleted";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $message]], $this->exceptionStatus);
        }
    }

    // Get All Taxes without paginate
    public function getMyAllTaxes(){
        try
        {
            $user = $this->user;
            $taxes = MarketplaceTax::where('user_id',$user->user_id)->get();
            return response()->json(['success' => $this->successStatus,
                                    'data' => $taxes,
                                    ], $this->successStatus);

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
}
