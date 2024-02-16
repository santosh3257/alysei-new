<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Http\Traits\UploadImageTrait;
use Modules\Marketplace\Entities\MarketplaceProduct; 
use Modules\Marketplace\Entities\MarketplaceStore;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $query = MarketplaceProduct::with('user')->with('store')->with('labels');
        if((isset($_GET['filter'])) && (!empty($_GET['filter']))){
            
            $query->where('marketplace_store_id',$_GET['filter']);
        }
        $products = $query->orderBy('marketplace_product_id', 'DESC')->paginate(10);
        $stores = MarketplaceStore::select('marketplace_store_id','name')->get();
       
        return view('marketplace::products.list', compact('products','stores'));
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
    public function show($id)
    {
        $product = MarketplaceProduct::with('user')->with('store')->with('labels')->with('product_gallery')->where('marketplace_product_id',$id)->first();
        // echo '<pre>';
        // print_r($product);
        // echo '</pre>';
        // die();
        return view('marketplace::products.view',compact('product'));
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
        $product = MarketplaceProduct::where('marketplace_product_id', $id)->first();
        if($product){
            $product->delete();
            $message = "Product has been deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }

    public function updateProductStatus(Request $request){
        try
        {  
            $productId = $request->id;
            $status = $request->status;

            $product = MarketplaceProduct::where('marketplace_product_id',$productId)->first();
            $updatedData['status'] = $status;

            MarketplaceProduct::where('marketplace_product_id',$productId)->update($updatedData);
            $message = "Status has been changed";

            return response()->json(array('success' => true, 'message' => $message));

        }catch(\Exception $e)
        {
            //dd($e->getMessage());
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
    }
}
