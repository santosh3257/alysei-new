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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

	/**Socket**/
	Route::post('post/comment', 'Api\SocketConnectionController@commentPost');
	Route::post('post/like', 'Api\SocketConnectionController@likeUnlikePost');
	Route::post('save/connection', 'Api\SocketConnectionController@saveConnection');
	Route::get('get/all/connections/{userid}/{postOwner?}', 'Api\SocketConnectionController@getAllConnections');
	Route::post('remove/socket/connection/{socketId}', 'Api\SocketConnectionController@removeSocketConnection');
	Route::get('get/post/comments', 'Api\SocketConnectionController@getPostComments');

	Route::post('reply/post/comment', 'Api\SocketConnectionController@replyPost');
	Route::get('get/comment/replies', 'Api\SocketConnectionController@getCommentReplies');

	/**********/
	
	Route::get('get/privacy/policy', 'PageController@privacyAndPolicy');
	Route::group(['middleware' => 'auth:api'], function(){

	/**Search**/
	Route::get('get/mycountry/states/{param?}', 'Api\SearchController@getStates');
	Route::get('get/all/hubs', 'Api\SearchController@getAllHubs');
	Route::get('get/field/value/{fieldId}', 'Api\SearchController@getFieldValues');
	Route::get('get/pickup/delivery/fields', 'Api\SearchController@getPickupOrDelivery');
	Route::get('search', 'Api\SearchController@search');

	Route::get('get/roles/by/hubid/{hubid}', 'Api\SearchController@getRolesByHub');
	Route::get('get/usersin/role', 'Api\SearchController@getUserInCurrentRole');

	Route::post('subscribe/unsubscribe/hub', 'Api\SearchController@subscribeOrUnsubscribeHub');	
	/*********/

	//upload media in chat
	Route::post('upload/media', 'Api\ChatController@uploadMedia');	


	Route::get('get/all/notifications', 'Api\NotificationController@getAllNotifications');


	Route::post('send/message/notification', 'Api\NotificationController@sendNewMessageNotification');	

	Route::post('delete/notifications', 'Api\NotificationController@deleteNotification');

	Route::delete('notification/delete/{id}', 'Api\NotificationController@deleteSingleNotification');


});
