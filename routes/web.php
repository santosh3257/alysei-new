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

Route::get('privacy-policy','PageController@privacyPolicy')->name('privacy-policy');
Route::get('terms','PageController@getTerms');
Route::get('/email/incomplete/producer/profile','SendEmailUsers@sendEmailIncompleteProfileProducer');

/*Route::get('/', function () {
   
    return redirect('login');
});
Route::get('/login', 'LoginController@index')->name('login');
Route::post('login/admin-login', 'LoginController@adminLogin');
Route::get('/logout', 'AdminController@logout');


//Route::get('/home', 'HomeController@index')->name('home');
Route::group(['prefix'=>'login','middleware'=>['web','isAdminLogin']], function(){

	Route::get('dashboard', 'AdminController@dashboard');
	Route::get('/users', 'UserController@userList');
	Route::get('/users/edit/{id}', 'UserController@edit');
	Route::post('/users/update/{id}', 'UserController@update');
	Route::get('/users/show/{id}', 'UserController@show');

});*/
Route::get('/apple-app-site-association', function () {
    $json = file_get_contents(base_path('.well-known/apple-app-site-association'));
    return response($json, 200)
        ->header('Content-Type', 'application/json');
});
/* If the app is not install following route will help to redirect to respective store.*/
Route::get('/login/username/{username}', function () {
    preg_match("/iPhone|Android|iPad|iPod|webOS/", $_SERVER['HTTP_USER_AGENT'], $matches);
    $os = current($matches);
    switch ($os) {
        case 'iPhone':
            return redirect('itms-apps://itunes.apple.com/us/app/my-patro/id1049173930');
            break;
        case 'Android':
            return redirect('https://play.app.goo.gl/?link=https://play.google.com/store/apps/details?id=com.cninfotech.nepalichords&hl=en');
            break;
        case 'iPad':
            return redirect('itms-apps://itunes.apple.com/us/app/my-patro/id1049173930');
            break;
        case 'iPod':
            return redirect('itms-apps://itunes.apple.com/us/app/my-patro/id1049173930');
            break;
        case 'webOS':
            return redirect('https://apps.apple.com/us/app/my-patro/id1049173930');
            break;
    }
});