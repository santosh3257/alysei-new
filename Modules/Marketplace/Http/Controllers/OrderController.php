<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Marketplace\Entities\MarketplaceStore;
use Modules\Marketplace\Entities\MarketplaceOrder;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function orderList()
    {
        $stores = MarketplaceStore::select('marketplace_store_id','name')->get();
        $soldQuantity = MarketplaceOrder::whereNotIn('status',['pending','cancelled','failed']);
        $totalSum = MarketplaceOrder::whereNotIn('status',['pending','cancelled','failed']);
        $totalOrder = MarketplaceOrder::whereNotIn('status',['pending','cancelled','failed']);
        $storeCount = MarketplaceOrder::whereNotIn('status',['pending','cancelled','failed'])->groupBy('store_id');

        $query = MarketplaceOrder::with('transactionInfo','productItemInfo.productInfo','getStore');
        if((isset($_GET['filter'])) && (!empty($_GET['filter']))){
            
            $query->where('store_id',$_GET['filter']);

            $soldQuantity->where('store_id',$_GET['filter']);
            $totalSum->where('store_id',$_GET['filter']);
            $totalOrder->where('store_id',$_GET['filter']);
            $storeCount->where('store_id',$_GET['filter']);
        }
        if((isset($_GET['from'])) && (!empty($_GET['from']))){
            $query->whereDate('created_at', '>=', $_GET['from']);

            $soldQuantity->whereDate('created_at', '>=', $_GET['from']);
            $totalSum->whereDate('created_at', '>=', $_GET['from']);
            $totalOrder->whereDate('created_at', '>=', $_GET['from']);
            $storeCount->whereDate('created_at', '>=', $_GET['from']);

            if((isset($_GET['to'])) && (empty($_GET['to']))){
                $query->whereDate('created_at', '<=', $_GET['from']);
    
                $soldQuantity->whereDate('created_at', '<=', $_GET['from']);
                $totalSum->whereDate('created_at', '<=', $_GET['from']);
                $totalOrder->whereDate('created_at', '<=', $_GET['from']);
                $storeCount->whereDate('created_at', '<=', $_GET['from']);
            }
        }
        if((isset($_GET['to'])) && (!empty($_GET['to']))){

            if((isset($_GET['from'])) && (empty($_GET['from']))){
                $query->whereDate('created_at', '>=', $_GET['to']);
    
                $soldQuantity->whereDate('created_at', '>=', $_GET['to']);
                $totalSum->whereDate('created_at', '>=', $_GET['to']);
                $totalOrder->whereDate('created_at', '>=', $_GET['to']);
                $storeCount->whereDate('created_at', '>=', $_GET['to']);
            }
            $query->whereDate('created_at', '<=', $_GET['to']);

            $soldQuantity->whereDate('created_at', '<=', $_GET['to']);
            $totalSum->whereDate('created_at', '<=', $_GET['to']);
            $totalOrder->whereDate('created_at', '<=', $_GET['to']);
            $storeCount->whereDate('created_at', '<=', $_GET['to']);
        }
        $orders = $query->orderBy('created_at','desc')->paginate(20);
        

        $soldQuantity = $soldQuantity->sum('num_items_sold');
        $totalSum = $totalSum->sum('total_seles');
        $totalOrder = $totalOrder->count();
        $storeCount = $storeCount->get();
        
        return view('marketplace::order.orderList', compact('orders','stores','soldQuantity','totalSum','totalOrder','storeCount'));
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
    public function viewOrder(Request $request, $id)
    {
        $order = MarketplaceOrder::with('sellerInfo','buyerInfo','transactionInfo','shippingAddress','billingAddress','productItemInfo.productInfo','productItemInfo.productInfo.productCategory','productItemInfo.productInfo.product_gallery','getStore')->where('order_id',$id)->first();
        // echo '<pre>';
        // print_r($order);
        // echo '</pre>';
        // die();
        return view('marketplace::order.view', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('marketplace::edit');
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
