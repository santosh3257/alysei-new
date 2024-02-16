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

/*Route::middleware('auth:api')->get('/recipe', function (Request $request) {
    return $request->user();
});*/

Route::group(['middleware' => ['auth:api','language']], function(){

    Route::get('get/recipe/categories', 'Api\RecipeController@getRecipeCategories');
    Route::get('get/recipe/ingredients', 'Api\RecipeController@getRecipeIngredients');
    Route::get('get/recipe/tools', 'Api\RecipeController@getRecipeTools');
    Route::get('get/recipe/regions', 'Api\RecipeController@getRecipeRegions');
    Route::get('get/recipe/meals', 'Api\RecipeController@getRecipeMeals');
    Route::get('get/recipe/courses', 'Api\RecipeController@getRecipeCourses');
    Route::get('get/child/ingredients/{parentId}', 'Api\RecipeController@getChildIngredients');

    Route::post('create/recipe', 'Api\RecipeController@createRecipe');
    Route::get('get/myrecipes', 'Api\RecipeController@getMyRecipes');
    Route::get('create/link/recipe','Api\RecipeController@createLink');
    
    Route::post('favourite/unfavourite/recipe', 'Api\RecipeController@makeFavouriteOrUnfavourite');
    Route::get('get/my/favourite/recipes', 'Api\RecipeController@getMyFavouriteRecipes');

    Route::post('add/ingredients', 'Api\RecipeController@addIngredient');
    Route::post('add/tools', 'Api\RecipeController@addTool');

    Route::get('get/cooking/skills', 'Api\RecipeController@getCookingSkills');
    Route::get('search/meal', 'Api\RecipeController@searchMeals');
    Route::get('get/diet/list', 'Api\RecipeController@getRecipeDiets');

    Route::get('get/recipe/detail/{id}', 'Api\RecipeController@getRecipeDetail');
    Route::get('get/food/intolerance', 'Api\RecipeController@getFoodIntolerance');

    Route::post('do/review', 'Api\RecipeController@doReview');
    Route::get('get/reviews', 'Api\RecipeController@getReviews');
    Route::get('get/home/screen', 'Api\RecipeController@getHomeScreen');
    Route::get('filter/recipe', 'Api\RecipeController@filterRecipe');

    Route::post('save/preference', 'Api\RecipeController@savePreferences');
    Route::get('get/saved/preferences', 'Api\RecipeController@getPreferences');
    Route::post('save/update/draft/recipe/{recipeId?}', 'Api\RecipeController@saveOrUpdateRecipeInDraft');
    Route::get('search/ingredients', 'Api\RecipeController@searchIngredients');
    Route::get('search/tools', 'Api\RecipeController@searchTools');
    Route::get('search/recipe', 'Api\RecipeController@searchRecipe');
    Route::post('update/recipe/{recipeId}', 'Api\RecipeController@updateRecipe');

    Route::post('delete/recipe/{recipeId}', 'Api\RecipeController@deleteRecipe');
    Route::post('update/review', 'Api\RecipeController@updateReview');

    // Get All Recipe Quantity
    Route::get('get/recipe/unit-quantity', 'Api\RecipeController@getRecipeQuantity');
    

});