<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/marketplace', function (Request $request) {
    return $request->user();
});*/

Route::group(['middleware' => 'auth:api'], function(){

	Route::get('get/store/prefilled/values', 'Api\StoreController@getPreFilledValues');
	Route::get('get/marketplace/walkthrough', 'Api\WalkthroughScreenController@getMarketplaceWalkThroughScreens');
	Route::get('get/marketplace/packages', 'Api\PackageController@getPackages');
	Route::get('get/marketplace/product/categories/{allCategories?}', 'Api\ProductController@getProductCategories');
	Route::get('get/marketplace/product/subcategories', 'Api\ProductController@getProductSubcategories');
	Route::get('get/marketplace/brand/label', 'Api\ProductController@getBrandLabels');
	Route::get('checkif/store/created', 'Api\StoreController@checkIfStoreCreated');
	Route::get('get/dashboard/screen/{filterType?}', 'Api\StoreController@getDashboardScreen');
	Route::get('download/marketplace/analyst/{filterType?}', 'Api\StoreController@downloadMarketPlaceAnalyst');

	Route::get('get/store/details', 'Api\StoreController@getStoreDetails');
	Route::post('update/store/details', 'Api\StoreController@updateStoreDetails');
	Route::post('update/product/details', 'Api\ProductController@updateProductDetails');
	Route::get('get/myproduct/list', 'Api\ProductController@getMyProductList');
	Route::get('get/product/tax-classes-list','Api\ProductController@getAllProductTaxClasses');
	Route::post('delete/product', 'Api\ProductController@deleteProduct');
	Route::post('change/product/status/{product_id}', 'Api\ProductController@changeProductStatus');

	Route::post('save/store', 'Api\StoreController@saveStoreDetails');
	Route::post('save/product', 'Api\ProductController@saveProductDetails');
	Route::post('delete/gallery/image', 'Api\StoreController@deleteGalleryImage');
	Route::get('search/product', 'Api\ProductController@searchProduct');
	Route::get('recent/search/product', 'Api\ProductController@recentSearchProduct');
	Route::get('get/product/detail', 'Api\ProductController@getProductDetail');
	Route::get('get/product/detail/by/slug', 'Api\ProductController@getProductDetailBySlug');
	Route::post('make/favourite/store/product', 'Api\FavouriteController@makeFavourite');
	Route::post('make/unfavourite/store/product', 'Api\FavouriteController@makeUnfavourite');

	Route::post('do/review/store/product', 'Api\RatingController@doReview');
	Route::get('get/all/reviews', 'Api\RatingController@getAllReviews');
	Route::get('get/seller/profile/{storeid?}', 'Api\StoreController@getSellerProfile');
	Route::get('get/search/product/listing', 'Api\ProductController@getSearchProductListing');
	Route::post('save/product/enquery', 'Api\ProductController@saveProductEnquery');
	Route::get('get/enqueries/{tab?}', 'Api\ProductController@getProductEnquery');
	Route::get('get/store/category/{storeid}/{categoryId?}', 'Api\StoreController@getStoreCategoryData');


	Route::get('get/box/detail/{boxId}', 'Api\HomepageController@getBoxDetails');

	Route::get('get/homescreen', 'Api\HomepageController@getHomeScreen');
	Route::get('get/products', 'Api\HomepageController@getProducts');
	Route::get('get/products/by/region', 'Api\HomepageController@getProductsByRegions');
	Route::get('get/products/by/category', 'Api\HomepageController@getProductsByCategory');
	Route::get('filter', 'Api\HomepageController@filter');

	Route::get('get/product/properties', 'Api\HomepageController@getProductProperties');
	Route::get('get/conservation/methods', 'Api\HomepageController@getConservationMethod');

	Route::get('get/all/entities/for/homescreen/{entityId}', 'Api\HomepageController@getAllEntities');
	Route::post('update/marketplace/rating', 'Api\RatingController@updateReview');
	Route::get('check/store/status', 'Api\StoreController@checkStoreStatus');
	
	//Marketplace enquery
	Route::post('send/enquiry/message', 'Api\ProductController@sendEnquiryMessage');
	Route::get('get/user/enquiries/{tab?}', 'Api\ProductController@getUserProductEnquiry');
	Route::post('get/enquiry/messages', 'Api\ProductController@getEnquiryMessages');
	Route::post('update/enquiry/status', 'Api\ProductController@updateEnquiryStatus');
	Route::get('get/conservation/properties/{fieldOptionId}','Api\HomepageController@getConservationAndProperties');

	// Tax
	Route::get('get/mytax','Api\TaxController@getMyTaxes');
	Route::get('get/alltaxes','Api\TaxController@getMyAllTaxes');
	Route::post('add/my-tax','Api\TaxController@AddMytax');
	Route::get('edit/my-tax/{tax_id}','Api\TaxController@editMytax');
	Route::post('update/my-tax/{tax_id}','Api\TaxController@updateMytax');
	Route::delete('delete/my-tax/{tax_id}','Api\TaxController@deleteMytax');

	// Tax Classes
	Route::get('get/tax/classes','Api\TaxClassesController@getTaxClasses');
	Route::post('add/tax/class','Api\TaxClassesController@addTaxClasses');
	Route::get('edit/tax-class/{tax_class_id}','Api\TaxClassesController@editMyTaxClass');
	Route::post('update/tax-class/{tax_class_id}','Api\TaxClassesController@updateMyTaxClass');
	Route::delete('delete/tax-class/{tax_class_id}','Api\TaxClassesController@deleteMyTaxClass');

	// Offers
	Route::get('get/my-offers','Api\ProductOfferController@getMyOffer');
	Route::post('offer/create','Api\ProductOfferController@addProductOffer');
	Route::get('edit/my-offer/{offer_id}','Api\ProductOfferController@editMyOffer');
	Route::post('update/my-offer/{offer_id}','Api\ProductOfferController@updateMyOffer');
	Route::get('get/my-product/list','Api\ProductOfferController@myProductsList');
	Route::post('change/offer/status','Api\ProductOfferController@importerChangeOfferStatus');
	Route::get('offer/view/{id}','Api\ProductOfferController@viewSingleOffer');
	Route::delete('delete/offer/{offer_id}','Api\ProductOfferController@deleteOffer');

	// Marketplace Orders
	Route::get('get/my-orders','Api\ProductOrderController@getMyOrders');
	Route::post('product/order','Api\ProductOrderController@makeNewOrder');
	Route::post('order/payment/status','Api\ProductOrderController@orderPaymentCompleted');
	Route::get('get/order/{order_id}', 'Api\ProductOrderController@singleOrderInfo');
	Route::post('change/order/status/{order_id}','Api\ProductOrderController@changeOrderStatus');
	Route::get('get/weekly/revenue','Api\ProductOrderController@weeklyRevenue');
	Route::post('verify/checkout/order','Api\ProductOrderController@verifyCheckoutOrder');
	Route::post('upload/order-invoice/{order_id}','Api\ProductOrderController@uploadOrderInvoice');
	

	// Marketplace transcations
	Route::get('get/my-transactions','Api\OrderTransactionController@myTransactionList');
	Route::get('get/transaction/{id}', 'Api\OrderTransactionController@singleTransactionInfo');

	//Payment Setting
	Route::get('get/payment/setting', 'Api\HomepageController@getPaymentSetting');
	Route::post('payment/setting', 'Api\HomepageController@paymentSetting');

	// My Customers 
	Route::get('get/my-customers','Api\ProductOrderController@getMyCustomers');
	Route::get('get/my-customer/{customer_id}','Api\ProductOrderController@getMyCustomerInfo');

	// shipping/Billing address Api
	Route::post('add/address','Api\ProductOrderController@addOrderAddress');
	Route::get('get/my-address','Api\ProductOrderController@getMyAddress');
	Route::put('update/my-address/{type}/{id}','Api\ProductOrderController@updateMyAddress');
	Route::delete('delete/{type}/{id}','Api\ProductOrderController@deleteMyAddress');

	// Get Importer Users
	Route::get('get/importer/lists','Api\HomepageController@getImporterUsersList');

	// Create Payment Intent
	Route::post('payment/intent','Api\ProductOrderController@createPaymentIntent');

	// Producer make Payment request for admin
	Route::get('producer/payment/request/{order_id}','Api\ProductOrderController@paymentRequestForAdmin');
	

});