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

Route::get('get/roles', 'Api\RegisterController@getRoles');
Route::get('get/walkthroughscreens/{role_id?}', 'Api\RegisterController@getWalkThroughScreens');
Route::get('get/registration/fields/{role_id}', 'Api\RegisterController@getRegistrationFormFields');
Route::post('user/register', 'Api\RegisterController@register');
Route::post('user/login', 'Api\LoginController@login');
Route::post('forgot/password', 'Api\ResetPasswordController@forgotPassword');
Route::post('verfiy/password/otp', 'Api\ResetPasswordController@verifyForgotPasswordOtp'); // not required
Route::post('reset/password', 'Api\ResetPasswordController@resetPassword');
Route::post('verify/otp', 'Api\RegisterController@verifyOtp');
Route::post('reset/password/verify/otp', 'Api\RegisterController@verifyOtpResetPassword');
Route::post('resend/otp', 'Api\RegisterController@resendOtp');


Route::get('get/states', 'Api\CountryController@getStates');
Route::get('get/cities', 'Api\CountryController@getCities');
Route::get('get/countries', 'Api\CountryController@getCountries');


Route::group(['middleware' => 'auth:api'], function(){

	
	Route::post('update/avatar/cover/image', 'Api\UserController@updateProfileCoverImage');

	Route::get('userinfo', 'Api\UserController@userinfo');
	Route::get('user/settings', 'Api\FeaturedListingsController@userSettings');
	Route::post('update/user/settings', 'Api\UserController@updateUserSettings');
	Route::post('change/password', 'Api\ResetPasswordController@changePassword');
	Route::get('logout', 'Api\LoginController@logout');
	Route::get('get/alysei/progress', 'Api\LoginController@alyseiProgress');

	Route::delete('account/delete/{id}', 'Api\UserController@deleteMyAccount');

	Route::post('update/contact/details', 'Api\UserController@updateContactDetails');
	Route::post('post/featured/listing', 'Api\FeaturedListingsController@postFeaturedListing');
	Route::get('get/featured/listing/{featuredListingid}', 'Api\FeaturedListingsController@editFeaturedListing');

	Route::get('get/hub/countries', 'Api\HubController@getHubCountries');
	Route::get('get/active/upcoming/countries', 'Api\HubController@getActiveAndUpcomingCountries');

	Route::get('get/selected/hub/countries', 'Api\HubController@getSelectedHubCountries');
	Route::get('get/selected/hub/states', 'Api\HubController@getSelectedHubStates');
	Route::delete('user/hubs/deleted', 'Api\HubController@userDeletedAllHubs');

	Route::post('get/hub/city', 'Api\HubController@getHubsCity');
	Route::get('get/state/wise/hubs', 'Api\HubController@getStateWiseHubs');
	Route::post('get/hubs', 'Api\HubController@getHubs');
	Route::post('post/hubs', 'Api\HubController@postUserHubs');
	Route::get('review/hubs', 'Api\HubController@hubsReviewSelection');
	Route::get('get/hub/{hubId}','Api\HubController@getSingleHub');
	Route::post('get/hubs', 'Api\HubController@getHubs');
	Route::get('get/my-country/hubs', 'Api\HubController@getMyCountryHubs');
	Route::post('set/notallow/hub/connection/request', 'Api\HubController@notAllowedConnectionRequest');

	Route::get('get/user/submited/fields/{visitorId?}', 'Api\UserController@getUserSubmitedFields');
	Route::get('get/user/submitted/fields/{visitorId?}', 'Api\UserController@getUserSubmitedFields');
	Route::post('update/user/profile', 'Api\UserController@updateUserProfile');

	Route::get('get/user/certificates', 'Api\UserController@getUserCertificates');
	Route::post('update/user/certificates', 'Api\UserController@updateUserCertificates');

	Route::get('get/member/profile', 'Api\UserController@getMemberProfile');
	Route::get('get/member/about/tab', 'Api\UserController@getMemberAboutTab');
	Route::get('get/member/contact/tab/{profileId?}', 'Api\UserController@getMemberContactTab');

	Route::get('get/all/featured/listing', 'Api\FeaturedListingsController@getAllFeaturedListing');
	
	Route::post('delete/featured/listing', 'Api\FeaturedListingsController@deleteFeaturedListing');
	Route::post('remove/cover/profile/image', 'Api\UserController@removeProfileCoverImage');

	Route::get('get/block/user/list', 'Api\BlockUserController@getBlockedUserList');
	Route::post('block/user', 'Api\BlockUserController@blockUser');
	Route::post('unblock/user', 'Api\BlockUserController@unBlockUser');

	Route::get('get/profile/progress', 'Api\UserController@getProfileProgress');
	Route::get('get/profile', 'Api\UserController@getProfile');

	Route::get('get/visitor/profile', 'Api\UserController@getVisitorProfile');

	//Route::get('get/featured/tabs', 'Api\UserController@getFeaturedTabs');
	//Blogs
	Route::get('get/blog/listing', 'Api\BlogController@getBlogListing');	
	Route::post('create/blog', 'Api\BlogController@createBlog');	
	Route::post('update/blog', 'Api\BlogController@updateBlog');	
	Route::post('delete/blog', 'Api\BlogController@deleteBlog');
	Route::post('blog/feedback','Api\BlogController@blogFeedback');
	Route::get('get/blog/feedback/{id}','Api\BlogController@getBlogFeedbacks');

	//events
	Route::get('get/event/listing', 'Api\EventController@getEventListing');	
	Route::post('create/event', 'Api\EventController@createEvent');
	Route::get('edit/event/{eventid}', 'Api\EventController@editEvent');
	Route::post('update/event', 'Api\EventController@updateEvent');
	Route::post('delete/event', 'Api\EventController@deleteEvent');

	//trips
	Route::get('get/trip/listing', 'Api\TripController@getTripListing');	
	Route::get('get/adventure/types', 'Api\TripController@getAdventureTypes');
	Route::get('get/adventure/alltypes', 'Api\TripController@getAdventureAllTypes');
	Route::get('get/intensity/list', 'Api\TripController@getIntensityList');
	Route::post('create/trip', 'Api\TripController@createTrip');
	Route::get('edit/trip/{tripid}', 'Api\TripController@editTrip');
	Route::post('update/trip', 'Api\TripController@updateTrip');
	Route::post('delete/trip', 'Api\TripController@deleteTrip');	

	Route::get('get/all/cousins', 'Api\UserController@getCousins');	

	//awards
	Route::get('get/award/listing', 'Api\AwardController@getAwardListing');	
	Route::get('get/medal/types', 'Api\AwardController@getMedalTypes');
	Route::post('create/award', 'Api\AwardController@createAward');
	Route::get('edit/award/{awardid}', 'Api\AwardController@editAward');
	Route::post('update/award', 'Api\AwardController@updateAward');
	Route::post('delete/award', 'Api\AwardController@deleteAward');
	Route::post('update/user/field', 'Api\UserController@updateUserFieldValues');

	Route::post('like/unlike/event', 'Api\EventController@likeUnlikeEvent');

	Route::get('get/notification/status', 'Api\UserController@getNotificationStatus');
	Route::post('update/notification/status', 'Api\UserController@updateNotificationStatus');

	Route::get('get/faq','Api\FaqController@getRoleFaq');

	Route::get('get/news','Api\NewsController@getAllNews');

	//Change Email Request
	Route::post('confirm/password', 'Api\UserController@confirmPassword');
	Route::post('change/email/request', 'Api\UserController@changeEmailRequest');
	Route::post('verify/email/request/otp', 'Api\UserController@verifyEmailRequestOtp');
	Route::get('resend/email/request/otp', 'Api\UserController@resendEmailRequestOtp');

	Route::post('report-user','Api\BlockUserController@reportUser');

	Route::post('search/hubs/my-regions','Api\HubController@searchHubsMyRegions');

});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
