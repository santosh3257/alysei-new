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
Route::group(['prefix'=>'dashboard/recipe','middleware'=>['web','isAdminLogin']], function(){
    Route::get('/', 'RecipeController@index');
    

    //Admin Ingredients Routes
    Route::get('/ingredients', 'IngredientsController@index');
    Route::get('/ingredient/add', 'IngredientsController@create');
    Route::post('/ingredient/store', 'IngredientsController@store');
    Route::get('/ingredient/edit/{id}', 'IngredientsController@edit');
    Route::post('/ingredient/update/{id}', 'IngredientsController@update');
    Route::get('/ingredient/delete/{id}', 'IngredientsController@destroy');

    //Admin Ingredients Routes
    Route::get('/preferences', 'PreferencesController@index');
    Route::get('/preference/add', 'PreferencesController@create');
    Route::post('/preference/store', 'PreferencesController@store');
    Route::get('/preference/edit/{id}', 'PreferencesController@edit');
    Route::post('/preference/update/{id}', 'PreferencesController@update');
    Route::get('/preference/delete/{id}', 'PreferencesController@destroy');

    //Admin Meal Routes
    Route::get('/meals', 'MealsController@index');
    Route::get('/meal/add', 'MealsController@create');
    Route::post('/meal/store', 'MealsController@store');
    Route::get('/meal/edit/{id}', 'MealsController@edit');
    Route::post('/meal/update/{id}', 'MealsController@update');
    Route::get('/meal/delete/{id}', 'MealsController@destroy');

    //Admin Regions Routes
    Route::get('/regions', 'RegionsController@index');
    Route::get('/region/add', 'RegionsController@create');
    Route::post('/region/store', 'RegionsController@store');
    Route::get('/region/edit/{id}', 'RegionsController@edit');
    Route::post('/region/update/{id}', 'RegionsController@update');
    Route::get('/region/delete/{id}', 'RegionsController@destroy');

    //Admin Tool Routes
    Route::get('/tools', 'ToolsController@index');
    Route::get('/tool/add', 'ToolsController@create');
    Route::post('/tool/store', 'ToolsController@store');
    Route::get('/tool/edit/{id}', 'ToolsController@edit');
    Route::post('/tool/update/{id}', 'ToolsController@update'); 
    Route::get('/tool/delete/{id}', 'ToolsController@destroy');

    //Admin Diet Routes
    Route::get('/diets', 'DietsController@index');
    Route::get('/diet/add', 'DietsController@create');
    Route::post('/diet/store', 'DietsController@store');
    Route::get('/diet/edit/{id}', 'DietsController@edit');
    Route::post('/diet/update/{id}', 'DietsController@update');
    Route::get('/diet/delete/{id}', 'DietsController@destroy');

    //Admin Courses Routes
    Route::get('/courses', 'CoursesController@index');
    Route::get('/course/add', 'CoursesController@create');
    Route::post('/course/store', 'CoursesController@store');
    Route::get('/course/edit/{id}', 'CoursesController@edit');
    Route::post('/course/update/{id}', 'CoursesController@update');
    Route::get('/course/delete/{id}', 'CoursesController@destroy');

    //Admin Unit Quantity Routes
    Route::get('/unit-quantity', 'UnitQuantityController@index');
    Route::get('/unit-quantity/add', 'UnitQuantityController@create');
    Route::post('/unit-quantity/store', 'UnitQuantityController@store');
    Route::get('/unit-quantity/edit/{id}', 'UnitQuantityController@edit');
    Route::post('/unit-quantity/update/{id}', 'UnitQuantityController@update');
    Route::get('/unit-quantity/delete/{id}', 'UnitQuantityController@destroy');

});
