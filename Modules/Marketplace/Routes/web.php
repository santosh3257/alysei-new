<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('download/marketplace/analyst/{filterType?}/{userId}', 'StatsController@downloadMarketPlaceAnalyst');

Route::group(['prefix'=>'dashboard/marketplace','middleware'=>['web','isAdminLogin']], function(){
    Route::get('/', 'MarketplaceController@index');
    

    //Admin Marketplace stores Routes
    //Route::get('/stores', 'StoreController@index');
    Route::get('/stores', 'StoreController@search');
    Route::get('/store/view/{id}', 'StoreController@view');
    Route::post('/store/approve/{id}', 'StoreController@changeStatusToApprove');
    Route::get('store/delete/{id}','StoreController@destroy');

    // Update store status
    Route::post('/stores/store-status','StoreController@updateStoreStatus');
    Route::get('/inco-terms','StoreController@getIncoTerms');
    Route::get('/add/inco-term','StoreController@AddIncoTerms');
    Route::post('/inco-terms/store','StoreController@createIncoTerms');
    Route::get('/inco-term/edit/{id}','StoreController@editIncoTerms');
    Route::post('/inco-term/update/{id}','StoreController@UpdateIncoTerms');
    // Admin Marketplace Banner 
    Route::get('/banners', 'TopBannerController@index');
    Route::get('/banner/edit/{id}', 'TopBannerController@edit');
    Route::post('/banner/update/{id}', 'TopBannerController@update');
    Route::get('/banner/delete/{id}','TopBannerController@destroy');
    Route::get('/banner/add', 'TopBannerController@create');
    //Route::post('banner/crop', 'TopBannerController@crop');
    Route::post('/banner/store', 'TopBannerController@store');

    // Admin Marketplace Products 
    Route::get('/products', 'ProductController@index');
    Route::get('/product/view/{id}', 'ProductController@show');
    Route::post('/product/update/{id}', 'ProductController@update');
    Route::get('product/delete/{id}','ProductController@destroy');
    
    // Update product status
    Route::post('/product/product-status','ProductController@updateProductStatus');

    //Admin Regions Routes
    Route::get('/regions', 'RegionsController@index');
    Route::get('/region/add', 'RegionsController@create');
    Route::post('/region/store', 'RegionsController@store');
    Route::get('/region/edit/{id}', 'RegionsController@edit');
    Route::post('/region/update/{id}', 'RegionsController@update');
    Route::get('/region/delete/{id}', 'RegionsController@destroy');

    Route::get('/orders','OrderController@orderList');
    Route::get('/order/view/{id}','OrderController@viewOrder');
    Route::get('/transactions','TransactionController@index');
    Route::post('/transaction/payment-status','TransactionController@updateAdminPaymentStatus');
});

