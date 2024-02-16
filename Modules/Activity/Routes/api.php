
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

/*Route::middleware('auth:api')->get('/activity', function (Request $request) {
    return $request->user();
});*/
Route::get('get/post/detail', 'Api\ActivityController@getPostDetails');
Route::group(['middleware' => 'auth:api'], function(){
	Route::post('add/post', 'Api\ActivityController@addPost');
	Route::post('edit/post', 'Api\ActivityController@editPost');
	Route::post('share/post', 'Api\ActivityController@sharePost');
	Route::post('delete/post', 'Api\ActivityController@deletePost');
	Route::post('delete/post/attachment/{attachmentId}', 'Api\ActivityController@deletePostAttachment');
	Route::get('get/products/for/connection', 'Api\ConnectUserController@getProductListToConnect');

	Route::get('view/connection', 'Api\ConnectUserController@viewConnectionRequestOfProducer');
	
	Route::post('comment/like', 'Api\ActivityController@saveCommentLiks');
	Route::post('delete/post/comment', 'Api\ActivityController@deletePostComment');
	Route::post('post/comment/update/{id}','Api\ActivityController@updatePostComment');
	

	Route::post('follow/user', 'Api\FollowUserController@followUnfollowUser');
	Route::get('get/followers', 'Api\FollowUserController@getFollowersList');
	Route::get('get/followings', 'Api\FollowUserController@getFollowingsList');

	Route::get('get/permissions', 'Api\ConnectUserController@getPermissions');

	Route::post('send/connection/request', 'Api\ConnectUserController@sendConnectionRequest'); 
	Route::post('accept/reject/connection/request/from/profile', 'Api\ConnectUserController@acceptRejectRequestFromProfile');

	Route::get('get/connection/tabs', 'Api\ConnectUserController@getConnectionTabs');
	Route::get('get/pending/recieved/request', 'Api\ConnectUserController@getMyPendingRecievedRequest');
	Route::get('get/pending/sent/request', 'Api\ConnectUserController@getMyPendingSentRequest');
	Route::post('accept/reject/request', 'Api\ConnectUserController@acceptOrRejectConnection');


	Route::get('get/all/user/post/{postType}', 'Api\ActivityController@getAllUserPosts');
	Route::get('get/activity/feed', 'Api\ActivityController@getActivityFeeds');
	Route::post('view/latest-news','Api\ActivityController@viewLatestNews');

	Route::get('get/roles/for/connection', 'Api\PrivacyController@getRolesForConnection');
	Route::get('get/user/privacy', 'Api\PrivacyController@getPrivacyData');
	Route::post('save/privacy', 'Api\PrivacyController@savePrivacy');
	Route::post('save/email/preference', 'Api\PrivacyController@saveEmailPreference');
	Route::get('get/circle/detail', 'Api\ActivityController@getCircleDetail');
	Route::get('get/stories/byfilter', 'Api\ActivityController@filterDiscoverStories');
	Route::get('get/story/detail', 'Api\ActivityController@getStoriesDetails');
	Route::get('get/discovery/post/{slug}', 'Api\ActivityController@getDiscoveryPosts');

	Route::get('get/specialization', 'Api\ActivityController@getSpecialization');
	Route::get('get/restaurant/types', 'Api\ActivityController@getRestaurantTypes');
	Route::get('get/all/hubs', 'Api\ActivityController@getAllHubs');
	Route::post('report/post', 'Api\ActivityController@reportPost');

});