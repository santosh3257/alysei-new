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

Route::prefix('user')->group(function() {
    Route::get('/', 'UserController@index');
});

Route::get('/', function () {
    //return view('welcome');
    return redirect('login');
});
Route::get('/login', 'LoginController@index')->name('login');
Route::post('login/admin-login', 'LoginController@adminLogin');
Route::get('/logout', 'AdminController@logout');


//Route::get('/home', 'HomeController@index')->name('home');
Route::group(['prefix'=>'dashboard','middleware'=>['web','isAdminLogin']], function(){

	Route::get('/dynamic-link','ActivityController@CreateDynamicLink');
	Route::get('/', 'AdminController@dashboard');
	Route::get('/users', 'UserController@list');
	Route::get('/users/edit/{id}', 'UserController@edit');
	Route::post('/users/update/{id}', 'UserController@update');
	Route::post('/users/update', 'UserController@update');
	Route::get('/users/show/{id}', 'UserController@show');
	Route::post('/update-progress/{user_id}', 'UserController@updateProgressStatus');
	Route::post('/admin/update/user/product-type', 'UserController@adminUpdateProductType');
	// Route::post('/users/search-users','UserController@searchUsersByEmail');

	Route::post('/user-status', 'UserController@userStatus');
	Route::get('/users/delete/{id}', 'UserController@userDelete');
	Route::post('/deleteusers','UserController@deleteAllUsers');

	Route::post('/review-status', 'UserController@reviewStatus');
	Route::post('/certified-status', 'UserController@certifiedStatus');
	Route::post('/recognised-status', 'UserController@recognisedStatus');
	Route::post('/qm-status', 'UserController@qmStatus');

	//Admin Hubs Routes
    Route::get('/users/hubs', 'AdminHubsController@index');
    Route::get('/user/hub/add', 'AdminHubsController@create');
    Route::post('/user/hub/store', 'AdminHubsController@store');
    Route::get('/user/hub/edit/{id}', 'AdminHubsController@edit');
    Route::post('/user/hub/update/{id}', 'AdminHubsController@update');
	Route::get('/user/hub/delete/{id}', 'AdminHubsController@destroy');

	//Admin Countries Route
	Route::get('/users/countries', 'CountryController@index');
	Route::get('/user/country/edit/{id}', 'CountryController@edit');
	Route::post('/user/country/update/{id}', 'CountryController@update');

	// Admin Roles Routes
	Route::get('users/roles','RoleController@index');
	Route::get('user/role/add','RoleController@create');
	Route::post('user/role/store','RoleController@store');
	Route::get('/user/role/edit/{id}', 'RoleController@edit');
    Route::post('/user/role/update/{id}', 'RoleController@update');
	Route::get('/user/role/delete/{id}', 'RoleController@destroy');

	// Admin Faq Routes
	Route::get('faq','FaqController@index');
	Route::get('faq/add', 'FaqController@create');
	Route::post('faq/store','FaqController@store');
	Route::get('faq/edit/{id}','FaqController@edit');
	Route::post('faq/update/{id}','FaqController@update');
	Route::get('faq/delete/{id}','FaqController@destroy');

	//Admin Feed Routes
	Route::get('feed','ActivityController@index');
	Route::get('feed/delete/{id}','ActivityController@destroy');
	Route::get('feed/view/{id}','ActivityController@show');
	Route::post('/delete/allfeed','ActivityController@deleteAllFeeds');

	//Admin Feed Routes
	Route::get('feed/spams','ActivityController@getSpamsActivity')->name('spams');
	Route::get('feed/delete/{id}','ActivityController@destroy');
	Route::get('feed/spam/view/{id}','ActivityController@showSpamActivity');


	// Admin News Routes
	Route::get('discover-alysei/news','NewsController@index');
	Route::get('discover-alysei/news/add', 'NewsController@create');
	Route::post('discover-alysei/news/store','NewsController@store');
	Route::get('discover-alysei/news/edit/{id}','NewsController@edit');
	Route::post('discover-alysei/news/update/{id}','NewsController@update');
	Route::get('discover-alysei/news/delete/{id}','NewsController@destroy');

	// Admin Walkthrough Routes
	Route::get('walkthrough','WalkthroughController@index');
	Route::get('walkthrough/add', 'WalkthroughController@create');
	Route::post('walkthrough/store','WalkthroughController@store');
	Route::get('walkthrough/edit/{id}','WalkthroughController@edit');
	Route::post('walkthrough/update/{id}','WalkthroughController@update');
	Route::get('walkthrough/delete/{id}','WalkthroughController@destroy');

	Route::get('market-place/walkthrough','MarketplaceWalkthroughController@index');
	Route::get('market-place/walkthrough/add', 'MarketplaceWalkthroughController@create');
	Route::post('market-place/walkthrough/store','MarketplaceWalkthroughController@store');
	Route::get('market-place/walkthrough/edit/{id}','MarketplaceWalkthroughController@edit');
	Route::post('market-place/walkthrough/update/{id}','MarketplaceWalkthroughController@update');
	Route::get('market-place/walkthrough/delete/{id}','MarketplaceWalkthroughController@destroy');

	// Admin Discover Alysei
	Route::get('discover-alysei/discovery-circle','DiscoverAlyseiController@index');
	Route::get('discover-alysei/discovery-circle/add', 'DiscoverAlyseiController@create');
	Route::post('discover-alysei/discovery-circle/store','DiscoverAlyseiController@store');
	Route::get('discover-alysei/discovery-circle/edit/{id}','DiscoverAlyseiController@edit');
	Route::post('discover-alysei/discovery-circle/update/{id}','DiscoverAlyseiController@update');
	Route::get('discover-alysei/discovery-circle/delete/{id}','DiscoverAlyseiController@destroy');

	// Discovery Posts
	Route::get('discover-alysei/discovery-posts','DiscoverAlyseiController@discoveryPost');
	Route::get('discover-alysei/discovery-post/create','DiscoverAlyseiController@discoveryCreatePost');
	Route::post('discover-alysei/discovery-post/store','DiscoverAlyseiController@storeDiscoveryPost');
	Route::get('discover-alysei/discovery-post/edit/{id}','DiscoverAlyseiController@discoveryEditPost');
	Route::post('discover-alysei/discovery-post/update/{id}','DiscoverAlyseiController@discoveryUpdatePost');
	Route::get('discover-alysei/discovery-post/delete/{id}','DiscoverAlyseiController@descoveryPostDestroy');

	// Admin register fields
	Route::get('registration/fields','RegistrationFieldController@index');
	Route::get('registration/field/add', 'RegistrationFieldController@create');
	Route::post('registration/field/store','RegistrationFieldController@store');
	Route::get('registration/field/edit/{id}','RegistrationFieldController@edit');
	Route::post('registration/field/update/{id}','RegistrationFieldController@update');
	Route::get('registration/field/delete/{id}','RegistrationFieldController@destroy');

	// Admin register field options
	Route::get('registration/field/options','RegistrationFieldOptionController@index');
	// Admin Award Medals
	Route::get('award-medals','AwardController@medalsList');
	Route::get('award-medal/add','AwardController@createMedal');
	Route::post('award-medal/store','AwardController@saveMedal');
	Route::get('award-medal/edit/{id}','AwardController@editMedal');
	Route::post('award-medal/update/{id}','AwardController@updateMedal');
	Route::get('award-medal/delete/{id}','AwardController@deleteMedal');

	// Admin Localization for throughout the website
	Route::get('localization', 'AdminController@getSiteLocalization');
	Route::get('localization/edit/{id}', 'AdminController@editSiteLocalization');
	Route::post('localization/update/{id}', 'AdminController@updateSiteLocalization');
	Route::get('localization/create', 'AdminController@createSiteLocalization');
	Route::post('localization/save', 'AdminController@saveSiteLocalization');
	Route::get('localization/delete/{id}','AdminController@deleteLocalization');

	//Admin property types
	Route::get('users/property-types', 'PropertyTypeController@index');
	Route::get('user/property-types/edit/{fieldId}/{optionId}/{option}', 'PropertyTypeController@edit');
	Route::post('user/property-types/update', 'PropertyTypeController@update');
	Route::post('user/property-types/delete/{optionId}', 'PropertyTypeController@destroy');
	Route::get('user/property-types/create', 'PropertyTypeController@create');
	Route::post('user/property-types/save', 'PropertyTypeController@store');
	Route::post('user/property-types/update-option', 'PropertyTypeController@updateOption');
	Route::get('users/property-types/temp-insert-property', 'PropertyTypeController@tempInsertProperty');
	Route::get('user/property-types/delete/confirm/{optionId}', 'PropertyTypeController@destroyConfirm');

	// Admin Restaurant Types
	Route::get('users/restaurant-types','RestaurantTypeController@index');
	Route::get('users/restaurant-types/create', 'RestaurantTypeController@create');
	Route::post('users/restaurant-types/save', 'RestaurantTypeController@store');
	Route::get('users/restaurant-types/edit/{user_field_option_id}', 'RestaurantTypeController@edit');
	Route::post('users/restaurant-types/update/{id}', 'RestaurantTypeController@update');
	Route::get('users/restaurant-types/delete/{id}', 'RestaurantTypeController@destroy');

	
	// Admin Expert Titles
	Route::get('users/expert-titles','VoiceOfExpertTitlesController@index');
	Route::get('users/expert-titles/create', 'VoiceOfExpertTitlesController@create');
	Route::post('users/expert-titles/save', 'VoiceOfExpertTitlesController@store');
	Route::get('users/expert-titles/edit/{user_field_option_id}', 'VoiceOfExpertTitlesController@edit');
	Route::post('users/expert-titles/update/{id}', 'VoiceOfExpertTitlesController@update');
	Route::get('users/expert-titles/delete/{id}', 'VoiceOfExpertTitlesController@destroy');

	// Admin Specility Trips
	Route::get('users/speciality-trips','SpecialityTripsController@index');
	Route::get('users/speciality-trips/create', 'SpecialityTripsController@create');
	Route::post('users/speciality-trips/save', 'SpecialityTripsController@store');
	Route::get('users/speciality-trips/edit/{user_field_option_id}', 'SpecialityTripsController@edit');
	Route::post('users/speciality-trips/update/{id}', 'SpecialityTripsController@update');
	Route::get('users/speciality-trips/delete/{id}', 'SpecialityTripsController@destroy');

	//
	Route::post('users/remove/hub', 'UserController@removeUserHub');
	Route::post('users/add/hubs', 'UserController@addUserHub');

	// Admin Hub Info Icon Routes
	Route::get('hub-infoicon','HubInfoIconController@index');
	Route::get('hub-infoicon/add', 'HubInfoIconController@create');
	Route::post('hub-infoicon/store','HubInfoIconController@store');
	Route::get('hub-infoicon/edit/{id}','HubInfoIconController@edit');
	Route::post('hub-infoicon/update/{id}','HubInfoIconController@update');
	Route::get('hub-infoicon/delete/{id}','HubInfoIconController@destroy');

	//Report User
	Route::get('users/user-report','UserController@getReportedUsers');

});
