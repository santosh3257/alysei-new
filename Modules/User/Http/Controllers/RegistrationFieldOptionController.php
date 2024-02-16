<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\UserField;
use Modules\User\Entities\UserFieldOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Support\Str;
use Modules\Marketplace\Entities\MarketplaceProduct;
use DB;

class RegistrationFieldOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $productCount = MarketplaceProduct::leftJoin('marketplace_review_ratings', 'marketplace_products.marketplace_product_id', '=', 'marketplace_review_ratings.id')->select('marketplace_products.title','marketplace_products.marketplace_product_id','marketplace_products.user_id',DB::raw('sum(marketplace_review_ratings.rating) as rating'),DB::raw('count(marketplace_review_ratings.marketplace_review_rating_id) as ratingcount'))->where('marketplace_products.user_id', 2021)->groupBy('marketplace_products.marketplace_product_id')->toSql(); 

        print_r($productCount);
        die();
        $fieldoptions = UserFieldOption::orderBy('user_field_option_id','desc')->paginate(10);
        return view('user::registration.fieldoption.list',compact('fieldoptions'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::create');
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
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('user::edit');
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
