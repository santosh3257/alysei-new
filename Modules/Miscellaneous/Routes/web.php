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

Route::group(['prefix'=>'dashboard','middleware'=>['web','isAdminLogin']], function(){
    Route::prefix('miscellaneous')->group(function() {
        Route::get('/', 'MiscellaneousController@index');
    }); 
Route::get('version-manager','VersionManageController@index');
Route::get('version-manager/create','VersionManageController@create');
Route::post('version-manager/save','VersionManageController@store');
Route::get('version-manager/edit/{id}','VersionManageController@edit');
Route::post('version-manager/update/{id}','VersionManageController@update');

// Cron Job Routes
Route::get('push-notifications','CronJobController@index');
Route::get('push-notification/create','CronJobController@create');
Route::post('push-notification/save','CronJobController@store');
Route::get('push-notification/cron/{id}','CronJobController@getCronProcessData');


});
