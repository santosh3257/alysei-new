<?php

namespace Modules\Marketplace\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\MarketplaceOrderTransaction;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Support\Facades\DB;

class OrderTransactionController extends Controller
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
     * Display a listing of the resource.
     * @return Response
     */
    public function myTransactionList(Request $request)
    {
        $user = $this->user;
        if($user->role_id == 3){
            $query = MarketplaceOrderTransaction::with('buyerInfo','orderInfo.shippingAddress','orderInfo.billingAddress','orderItemInfo.productInfo.productCategory','orderItemInfo.productInfo.product_gallery')->where('seller_id',$user->user_id)->orderBy('created_at','desc');
            if(!empty($request->start_date) && !empty($request->end_date)){
                $query->whereDate('created_at','>=',$request->start_date)->whereDate('created_at','<=',$request->end_date);
                //$query->whereDateBetween('created_at',$request->start_date,$request->end_date);
            }
            $myTransactions = $query->paginate(20);
        }
        else{
            $myTransactions = MarketplaceOrderTransaction::with('sellerInfo','orderInfo.shippingAddress','orderInfo.billingAddress','orderItemInfo.productInfo.productCategory','orderItemInfo.productInfo.product_gallery')->where('buyer_id',$user->user_id)->orderBy('created_at','desc')->paginate(20);
        }
        return response()->json(['success' => $this->successStatus,
                                'data' => $myTransactions,
                                ], $this->successStatus);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('marketplace::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function singleTransactionInfo($id)
    {
        $user = $this->user;
        if($user->role_id == 3){
            $myTransaction = MarketplaceOrderTransaction::with('buyerInfo','orderInfo.shippingAddress','orderInfo.billingAddress','orderInfo','orderItemInfo.productInfo.productCategory','orderItemInfo.productInfo.product_gallery')->where('seller_id',$user->user_id)->where('id',$id)->first();
        }
        else{
            $myTransaction = MarketplaceOrderTransaction::with('sellerInfo','orderInfo.shippingAddress','orderInfo.billingAddress','orderItemInfo.productInfo.productCategory','orderItemInfo.productInfo.product_gallery')->where('buyer_id',$user->user_id)->where('id',$id)->first();
        }
        return response()->json(['success' => $this->successStatus,
                                'data' => $myTransaction,
                                ], $this->successStatus);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
