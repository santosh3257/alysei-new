<?php

namespace Modules\Recipe\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\DeviceToken; 
use Modules\Recipe\Entities\RecipeCategory; 
use Modules\Recipe\Entities\RecipeCourse;
use Modules\Recipe\Entities\RecipeIngredient; 
use Modules\Recipe\Entities\RecipeMeal; 
use Modules\Recipe\Entities\RecipeRegion; 
use Modules\Recipe\Entities\RecipeTool; 
use Modules\Recipe\Entities\Recipe; 
use Modules\Recipe\Entities\RecipeFavourite;
use Modules\Recipe\Entities\RecipeSavedCategory;
use Modules\Recipe\Entities\RecipeCookingSkill;
use Modules\Recipe\Entities\RecipeDiet;
use Modules\Recipe\Entities\RecipeStep;
use Modules\Recipe\Entities\RecipeSavedIngredient;
use Modules\Recipe\Entities\RecipeSavedTool;
use Modules\Recipe\Entities\RecipeMapStepTool;
use Modules\Recipe\Entities\RecipeMapStepIngredient;
use Modules\Recipe\Entities\RecipeEnquery;
use Modules\Recipe\Entities\RecipeFoodIntolerance;
use Modules\Recipe\Entities\RecipeReviewRating;
use Modules\Recipe\Entities\Preference;
use Modules\Recipe\Entities\PreferenceMapCousin;
use Modules\User\Entities\Cousin;
use Modules\Recipe\Entities\PreferenceMapIntolerance;
use Modules\Recipe\Entities\PreferenceMapDiet;
use Modules\Recipe\Entities\PreferenceMapIngredient;
use Modules\Recipe\Entities\PreferenceMapCookingSkill;
use Modules\Recipe\Entities\PreferenceMapUser;
use Modules\Recipe\Entities\UnitQuantity;
use App\Notification;
use App\Attachment;
use DB;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Http\Traits\UploadImageTrait;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Kreait\Firebase\Factory;
//use App\Events\UserRegisterEvent;

class RecipeController extends CoreController
{
    use UploadImageTrait;
    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;
    public $unauthorisedStatus = 401;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    public function directLinkCon(){
        $factory = (new Factory)
        ->withServiceAccount(storage_path('/credentials/firebase_credential.json'))
        ->withDatabaseUri(env('FIREBASE_URL'));
        $dynamicLinksDomain = 'https://devalysei.page.link';
        $dynamicLinks = $factory->createDynamicLinksService($dynamicLinksDomain);
        return $dynamicLinks;
    }

    /*
     * Get recipie food intolerance
     * 
     */
    public function getFoodIntolerance(Request $request)
    {
        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $name = "name_".$lang;
            $user = $this->user;

            $preference = Preference::where('preference_id',2)->first();

            $tolerances = RecipeFoodIntolerance::with('image_id')->get();
            if(count($tolerances) > 0)
            {
                foreach($tolerances as $key => $tolerance)
                {
                    $tolerances[$key]->name = $this->translate('messages.'.$tolerance->name,$tolerance->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($tolerances),
                                        'data' => $tolerances,
                                        'title' => $preference->$name
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No food tolerance found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie categories
     * 
     */
    public function getRecipeCategories()
    {
        try
        {
            $user = $this->user;

            $categories = RecipeCategory::with('image_id')->where('status', '1')->orderBy('name','ASC')->get();
            if(count($categories) > 0)
            {
                foreach($categories as $key => $category)
                {
                    $categories[$key]->name = $this->translate('messages.'.$category->name,$category->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($categories),
                                        'data' => $categories,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No categories found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie diets
     * 
     */
    public function getRecipeDiets(Request $request)
    {
        try
        {
            $user = $this->user;
            $lang = 'en';
            $language = $request->header('locale');
            if(!empty($language)){
                $lang = $language;
            }

            $name = "name_".$lang;
            $preference = Preference::where('preference_id',3)->first();
            $diets = RecipeDiet::select('recipe_diet_id','name_'.$lang.' as name','image_id','created_at','updated_at','featured','priority')->with('image_id')->orderBy('priority','ASC')->orderBy('name','ASC')->get();
            if(count($diets) > 0)
            {
                // foreach($diets as $key => $diet)
                // {
                //     $diets[$key]->name = $this->translate('messages.'.$diet->name,$diet->name);
                // }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($diets),
                                        'data' => $diets,
                                        'title' => $preference->$name
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No recipe diets found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie categories
     * 
     */
    public function getRecipeCourses(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $courses = RecipeCourse::select('recipe_course_id','name_'.$lang.' as name','featured','priority','created_at','updated_at')->orderBy('priority','ASC')->orderBy('name','ASC')->get();
            if(count($courses) > 0)
            {
                foreach($courses as $key => $course)
                {
                    $courses[$key]->name = $this->translate('messages.'.$course->name,$course->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($courses),
                                        'data' => $courses,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No courses found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Recipe ingredients
     * 
     */
    public function getRecipeIngredients(Request $request)
    {
        try
        {
            $user = $this->user;

            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $title = 'title_'.$lang;
            $name = 'name_'.$lang;
            $preference = Preference::where('preference_id',4)->first();

            $parentIngredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent', 0)->orderBy($title,'ASC')->get();
            if(count($parentIngredients) > 0)
            {
                $categoriesWithCount = [];
                foreach($parentIngredients as $key => $parentIngredient)
                {
                    //$parentIngredients[$key]->title = $this->translate('messages.'.$parentIngredient->title,$parentIngredient->title);

                    $childIngredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent', $parentIngredient->recipe_ingredient_id)->get();

                    $childIngredientCounts = RecipeIngredient::with('image_id')->where('parent', $parentIngredient->recipe_ingredient_id)->count();
                    foreach($childIngredients as $keys => $childIngredient)
                    {
                        $childIngredients[$keys]->title = $this->translate('messages.'.$childIngredient->title,$childIngredient->title);    
                    }
                    $parentIngredients[$key]->ingredients = $childIngredients;

                    $categoriesWithCount[] = ['ingredient_types' => $parentIngredient->title, 'name' => $parentIngredient->name, 'count' => $childIngredientCounts];
                    
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($parentIngredients),
                                        'types' => $categoriesWithCount,
                                        'data' => $parentIngredients,
                                        'title' => $preference->$name
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No ingredients found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
    Get child ingresients
    *
    *
    */

    public function getChildIngredients(Request $request,$parentId = '')
    {
        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $title = 'title_'.$lang;
            $childIngredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent', $parentId)->orderBy($title,'ASC')->get();
            foreach($childIngredients as $keys => $childIngredient)
            {
                $childIngredients[$keys]->title = $this->translate('messages.'.$childIngredient->title,$childIngredient->title);    
            }
            return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($childIngredients),
                                        'data' => $childIngredients,
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
        
    }

    /*
     * Search meals
     * 
     */
    public function searchMeals(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            
            $ingredients = RecipeMeal::select('recipe_meal_id','name_'.$lang.' as name','image_id','featured','priority','created_at','updated_at')->with('image_id')->where('name_'.$lang,'LIKE','%'.$request->keyword.'%')->orderBy('name','ASC')->get();
            
            if(count($ingredients) > 0)
            {
                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($ingredients),
                                        'data' => $ingredients,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No meals found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Search ingredients
     * 
     */
    public function searchIngredients(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->type == 1)
            {
                $ingredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent','==', 0)->where("title_$lang",'LIKE','%'.$request->keyword.'%')->orderBy("title_$lang",'ASC')->get();    
            }
            else
            {
                $ingredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent','!=', 0)->where("title_$lang",'LIKE','%'.$request->keyword.'%')->orderBy("title_$lang",'ASC')->get();
            }
            
            if(count($ingredients) > 0)
            {
                $categoriesWithCount = [];
                // foreach($ingredients as $key => $ingredient)
                // {
                //     $ingredients[$key]->title = $this->translate('messages.'.$ingredient->title, $ingredient->title);
                // }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($ingredients),
                                        'data' => $ingredients,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No ingredients found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Search tools
     * 
     */
    public function searchTools(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $validator = Validator::make($request->all(), [ 
                'keyword' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            if($request->type == 1)
            {
                $tools = RecipeTool::select('recipe_tool_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent','==', 0)->where("title_$lang",'LIKE','%'.$request->keyword.'%')->orderBy("title_$lang",'ASC')->get();    
            }
            else
            {
                $tools = RecipeTool::select('recipe_tool_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent','!=', 0)->where("title_$lang",'LIKE','%'.$request->keyword.'%')->orderBy("title_$lang",'ASC')->get();
            }
            
            if(count($tools) > 0)
            {
                // foreach($tools as $key => $tool)
                // {
                //     $tools[$key]->title = $this->translate('messages.'.$tool->title, $tool->title);
                // }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($tools),
                                        'data' => $tools,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No tools found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie meals
     * 
     */
    public function getRecipeMeals(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $meals = RecipeMeal::select('recipe_meal_id','name_'.$lang.' as name','image_id','featured','priority','created_at','updated_at')->orderBy('name_'.$lang,'ASC')->get();
            if(count($meals) > 0)
            {
                // foreach($meals as $key => $meal)
                // {
                //     $meals[$key]->name = $this->translate('messages.'.$meal->name,$meal->name);
                // }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($meals),
                                        'data' => $meals,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No meals found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Cooking skills
     * 
     */
    public function getCookingSkills(Request $request)
    {
        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $name = "name_".$lang;
            $user = $this->user;
            $preference = Preference::where('preference_id',5)->first();

            $skills = RecipeCookingSkill::with('image_id')->get();
            if(count($skills) > 0)
            {
                foreach($skills as $key => $skill)
                {
                    $skills[$key]->name = $this->translate('messages.'.$skill->name,$skill->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($skills),
                                        'data' => $skills,
                                        'title'=>$preference->$name
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No cooking skills found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get recipie categories
     * 
     */
    public function getRecipeRegions(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $validator = Validator::make($request->all(), [ 
                'cousin_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $regions = RecipeRegion::select('recipe_region_id','cousin_id','name_'.$lang.' as name','image_id','featured','priority','created_at','updated_at')->with('image_id')->where('cousin_id', $request->cousin_id)->orderBy('name','ASC')->get();
            if(count($regions) > 0)
            {
                foreach($regions as $key => $region)
                {
                    $regions[$key]->name = $this->translate('messages.'.$region->name,$region->name);
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($regions),
                                        'data' => $regions,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No regions found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get Permission For Sending Requests
     * 
     */
    public function getRecipeTools(Request $request)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $parentTools = RecipeTool::select('recipe_tool_id','title_'.$lang.' as title','name', 'image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent', 0)->orderBy("title_$lang",'ASC')->get();
            if(count($parentTools) > 0)
            {
                $categoriesWithCount = [];
                foreach($parentTools as $key => $parentTool)
                {
                    //$parentTools[$key]->title = $this->translate('messages.'.$parentTool->title,$parentTool->title);

                    $childTools = RecipeTool::select('recipe_tool_id','title_'.$lang.' as title','name', 'image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent', $parentTool->recipe_tool_id)->orderBy("title_$lang",'ASC')->get();

                    $childIngredientCounts = RecipeTool::with('image_id')->where('parent', $parentTool->recipe_tool_id)->count();
                    foreach($childTools as $keys => $childTool)
                    {
                        $childTools[$keys]->title = $this->translate('messages.'.$childTool->title,$childTool->title);    
                    }
                    $parentTools[$key]->tools = $childTools;

                    $categoriesWithCount[] = ['tool_types' => $parentTool->title, 'name' => $parentTool->name, 'count' => $childIngredientCounts];
                    
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($parentTools),
                                        'types' => $categoriesWithCount,
                                        'data' => $parentTools,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No tools found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Delete recipe
     * 
     */
    public function deleteRecipe($recipeId='')
    {
        try
        {
            $user = $this->user;

            $getRecipe = Recipe::where('recipe_id', $recipeId)->where('user_id', $user->user_id)->first();
            if(!empty($getRecipe))
            {
                RecipeSavedIngredient::where('recipe_id', $recipeId)->delete();
                RecipeSavedTool::where('recipe_id', $recipeId)->delete();
                RecipeStep::where('recipe_id', $recipeId)->delete();
                RecipeMapStepIngredient::where('recipe_id', $recipeId)->delete();
                RecipeMapStepTool::where('recipe_id', $recipeId)->delete();
                Recipe::where('recipe_id', $recipeId)->delete();

                $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->where('user_id', $user->user_id)->orderBy('recipe_id', 'DESC')->get();
                
                foreach($myRecipes as $key => $myRecipe)
                {
                    $avgRating = RecipeReviewRating::where('recipe_id', $myRecipe->recipe_id)->avg('rating');
                    $totalLikes = RecipeFavourite::where('recipe_id', $myRecipe->recipe_id)->count();

                    $myRecipes[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $myRecipes[$key]->total_likes = $totalLikes;
                    
                }
                $message = "Recipe removed successfully";
                return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                        'count' =>  count($myRecipes),
                                        'data' => $myRecipes,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Create recipe
     * 
     */
    public function createRecipe(Request $request)
    {
        try
        {
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateData($requestedFields);
            
            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            //recipe detail
            $recipe = new Recipe;
            $recipe->user_id = $user->user_id;
            $recipe->name = $requestedFields['name'];
            $recipe->slug = SlugService::createSlug(Recipe::class, 'slug', $requestedFields['name']);
            $recipe->meal_id = $requestedFields['meal_id'];
            $recipe->course_id = $requestedFields['course_id'];
            $recipe->hours = $requestedFields['hours'];
            $recipe->minutes = $requestedFields['minutes'];
            $recipe->preparation_time_mins = ($requestedFields['hours'] * 60) + $requestedFields['minutes'];
            $recipe->serving = $requestedFields['serving'];
            $recipe->cousin_id = $requestedFields['cousin_id'];
            if($requestedFields['diet_id'] != null){
                $recipe->diet_id = $requestedFields['diet_id'];
            }
            if($requestedFields['intolerance_id'] != null)
            {
            $recipe->intolerance_id = $requestedFields['intolerance_id'];
            }
            $recipe->cooking_skill_id = $requestedFields['cooking_skill_id'];
            $recipe->region_id = $requestedFields['region_id'];
            //$recipe->image_id = $this->createImage($requestedFields['image_id']);
            //return $recipe->image_id;
            $recipe->image_id = $this->uploadRecipeImage($requestedFields['image_id']);
            $recipe->status = '1';
            $recipe->save();

            //save category
            /*foreach($requestedFields['save_categories'] as $savedCategory)
            {
                $categories = new RecipeSavedCategory;
                $categories->recipe_id = $recipe->id;
                $categories->category_id = $savedCategory;
                $categories->save();
            }*/

            //save ingredients
            foreach($requestedFields['saved_ingredients'] as $savedIngredients)
            {
                $savedIng = new RecipeSavedIngredient;
                $savedIng->recipe_id = $recipe->recipe_id;
                $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                $savedIng->quantity = $savedIngredients['quantity'];
                $savedIng->unit = $savedIngredients['unit'];
                $savedIng->save();
            }
            

            //save tools
            foreach($requestedFields['saved_tools'] as $savedTools)
            {
                $savedTool = new RecipeSavedTool;
                $savedTool->recipe_id = $recipe->recipe_id;
                $savedTool->tool_id = $savedTools['tool_id'];
                //$savedTool->quantity = $savedTools['quantity'];
                //$savedTool->unit = $savedTools['unit'];
                $savedTool->save();
            }
            

            //steps
            foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
            {
                $step = new RecipeStep;
                $step->recipe_id = $recipe->recipe_id;
                $step->title = $recipeStep['title'];
                $step->description = $recipeStep['description'];
                $step->save();

                foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                {
                    $mapIngredientSteps = new RecipeMapStepIngredient;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                    $mapIngredientSteps->save();
                }

                foreach($recipeStep['tools'] as $recipeStepstool)
                {
                    $mapIngredientSteps = new RecipeMapStepTool;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_tool_id = $recipeStepstool;
                    $mapIngredientSteps->save();
                }
                

            }
            $parameters = [
                'dynamicLinkInfo' => [
                    'domainUriPrefix' => 'https://devalysei.page.link',
                    'link' => 'https://dev.alysei.com/recipes/recipe-view/'. $recipe->recipe_id,
                    'androidInfo' => [
                        'androidPackageName' => 'com.alysei',
                    ],
                    "iosInfo" => [
                    'iosBundleId'=> 'com.app.Alysei',
                    'iosAppStoreId' => '1634783265',
                    ],
                ],
                'suffix' => ['option' => 'SHORT'],
            ];

            $link = $this->directLinkCon()->createDynamicLink($parameters);
            Recipe::where('recipe_id', $recipe->recipe_id)->update(['no_of_ingredients' => count($requestedFields['saved_ingredients']), 'directLink' => $link]);
            $message = "Your recipe has been saved successfully";
            return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Create recipe
     * 
     */
    public function updateRecipe(Request $request, $recipeId)
    {
        try
        {
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateData($requestedFields);
            
            $validator = Validator::make($requestedFields, $rules);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            RecipeSavedIngredient::where('recipe_id', $recipeId)->delete();
            RecipeSavedTool::where('recipe_id', $recipeId)->delete();
            RecipeStep::where('recipe_id', $recipeId)->delete();
            RecipeMapStepIngredient::where('recipe_id', $recipeId)->delete();
            RecipeMapStepTool::where('recipe_id', $recipeId)->delete();

            //recipe detail
            $recipe = Recipe::where('recipe_id', $recipeId)->first();
            $recipe->name = $requestedFields['name'];
            $recipe->meal_id = $requestedFields['meal_id'];
            $recipe->course_id = $requestedFields['course_id'];
            $recipe->hours = $requestedFields['hours'];
            $recipe->minutes = $requestedFields['minutes'];
            $recipe->preparation_time_mins = ($requestedFields['hours'] * 60) + $requestedFields['minutes'];
            $recipe->serving = $requestedFields['serving'];
            $recipe->cousin_id = $requestedFields['cousin_id'];
            $recipe->diet_id = $requestedFields['diet_id'];
            if(!empty($requestedFields['intolerance_id']))
            $recipe->intolerance_id = $requestedFields['intolerance_id'];
            $recipe->cooking_skill_id = $requestedFields['cooking_skill_id'];
            $recipe->region_id = $requestedFields['region_id'];
            $recipe->status = isset($requestedFields['status']) ? $requestedFields['status'] :1;
            if(!empty($requestedFields['image_id']))
            $recipe->image_id = $this->uploadRecipeImage($requestedFields['image_id']);
            //$this->uploadImage($request->file($requestedFields['image_id']));
            $recipe->save();
            //save category
            /*foreach($requestedFields['save_categories'] as $savedCategory)
            {
                $categories = new RecipeSavedCategory;
                $categories->recipe_id = $recipe->id;
                $categories->category_id = $savedCategory;
                $categories->save();
            }*/
            
            //save ingredients
            foreach($requestedFields['saved_ingredients'] as $savedIngredients)
            {
                $savedIng = new RecipeSavedIngredient;
                $savedIng->recipe_id = $recipe->recipe_id;
                $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                $savedIng->quantity = $savedIngredients['quantity'];
                $savedIng->unit = $savedIngredients['unit'];
                $savedIng->save();
            }
            

            //save tools
            foreach($requestedFields['saved_tools'] as $savedTools)
            {
                $savedTool = new RecipeSavedTool;
                $savedTool->recipe_id = $recipe->recipe_id;
                $savedTool->tool_id = $savedTools['tool_id'];
                //$savedTool->quantity = $savedTools['quantity'];
                //$savedTool->unit = $savedTools['unit'];
                $savedTool->save();
            }
            

            //steps
            foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
            {
                $step = new RecipeStep;
                $step->recipe_id = $recipe->recipe_id;
                $step->title = $recipeStep['title'];
                $step->description = $recipeStep['description'];
                $step->save();

                foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                {
                    $mapIngredientSteps = new RecipeMapStepIngredient;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                    $mapIngredientSteps->save();
                }

                foreach($recipeStep['tools'] as $recipeStepstool)
                {
                    $mapIngredientSteps = new RecipeMapStepTool;
                    $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                    $mapIngredientSteps->recipe_step_id = $step->id;
                    $mapIngredientSteps->recipe_saved_tool_id = $recipeStepstool;
                    $mapIngredientSteps->save();
                }
                

            }
            Recipe::where('recipe_id', $recipe->recipe_id)->update(['no_of_ingredients' => count($requestedFields['saved_ingredients'])]);
            $message = "Your recipe has been updated successfully";
            return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Create recipe
     * 
     */
    public function saveOrUpdateRecipeInDraft(Request $request, $recipeId = '')
    {
        try
        {
            $user = $this->user;

            $requestedFields = $request->params;
            $rules = $this->validateData($requestedFields);
            
            if($recipeId > 0)
            {
                RecipeSavedIngredient::where('recipe_id', $recipeId)->delete();
                RecipeSavedTool::where('recipe_id', $recipeId)->delete();
                RecipeStep::where('recipe_id', $recipeId)->delete();
                RecipeMapStepIngredient::where('recipe_id', $recipeId)->delete();
                RecipeMapStepTool::where('recipe_id', $recipeId)->delete();
                $recipe = Recipe::where('recipe_id', $recipeId)->first();
            }
            else
            {
                $recipe = new Recipe;
            }

            //recipe detail
            
            $recipe->user_id = $user->user_id;
            $recipe->name = $requestedFields['name'];
            $recipe->slug = SlugService::createSlug(Recipe::class, 'slug', $requestedFields['name']);
            $recipe->meal_id = $requestedFields['meal_id'];
            $recipe->course_id = $requestedFields['course_id'];
            $recipe->hours = $requestedFields['hours'];
            $recipe->minutes = $requestedFields['minutes'];
            $recipe->preparation_time_mins = ($requestedFields['hours'] * 60) + $requestedFields['minutes'];
            $recipe->serving = $requestedFields['serving'];
            $recipe->cousin_id = $requestedFields['cousin_id'];
            $recipe->diet_id = $requestedFields['diet_id'];
            if(!empty($requestedFields['intolerance_id']))
            $recipe->intolerance_id = $requestedFields['intolerance_id'];
            $recipe->cooking_skill_id = $requestedFields['cooking_skill_id'];
            $recipe->region_id = $requestedFields['region_id'];
            if(!empty($requestedFields['image_id']))
            $recipe->image_id = $this->createImage($requestedFields['image_id']);
            //return $recipe->image_id;
            //$this->uploadImage($request->file($requestedFields['image_id']));
            $recipe->status = $requestedFields['status'];
            $recipe->save();

            

            //save category
            /*if(count($requestedFields['save_categories']) > 0)
            {
                foreach($requestedFields['save_categories'] as $savedCategory)
                {
                    $categories = new RecipeSavedCategory;
                    $categories->recipe_id = $recipe->id;
                    $categories->category_id = $savedCategory;
                    $categories->save();
                }
            }*/

            if(count($requestedFields['saved_ingredients']) > 0)
            {
                //save ingredients
                foreach($requestedFields['saved_ingredients'] as $savedIngredients)
                {
                    $savedIng = new RecipeSavedIngredient;
                    $savedIng->recipe_id = $recipe->recipe_id;
                    $savedIng->ingredient_id = $savedIngredients['ingredient_id'];
                    $savedIng->quantity = $savedIngredients['quantity'];
                    $savedIng->unit = $savedIngredients['unit'];
                    $savedIng->save();
                }       
            }
            
            

            if(count($requestedFields['saved_tools']) > 0)
            {
                //save tools
                foreach($requestedFields['saved_tools'] as $savedTools)
                {
                    $savedTool = new RecipeSavedTool;
                    $savedTool->recipe_id = $recipe->recipe_id;
                    $savedTool->tool_id = $savedTools['tool_id'];
                    //$savedTool->quantity = $savedTools['quantity'];
                    //$savedTool->unit = $savedTools['unit'];
                    $savedTool->save();
                }    
            }
            
            

            if(count($requestedFields['recipe_steps']) > 0)
            {
                //steps
                foreach($requestedFields['recipe_steps'] as $key => $recipeStep)
                {
                    $step = new RecipeStep;
                    $step->recipe_id = $recipe->recipe_id;
                    $step->title = $recipeStep['title'];
                    $step->description = $recipeStep['description'];
                    $step->save();

                    if(count($recipeStep['ingredients']) > 0)
                    {
                        foreach($recipeStep['ingredients'] as $recipeStepsIngredient)
                        {
                            $mapIngredientSteps = new RecipeMapStepIngredient;
                            $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                            $mapIngredientSteps->recipe_step_id = $step->id;
                            $mapIngredientSteps->recipe_saved_ingredient_id = $recipeStepsIngredient;
                            $mapIngredientSteps->save();
                        }    
                    }   
                    
                    if(count($recipeStep['tools']) > 0)
                    {
                        foreach($recipeStep['tools'] as $recipeStepstool)
                        {
                            $mapIngredientSteps = new RecipeMapStepTool;
                            $mapIngredientSteps->recipe_id = $recipe->recipe_id;
                            $mapIngredientSteps->recipe_step_id = $step->id;
                            $mapIngredientSteps->recipe_saved_tool_id = $recipeStepstool;
                            $mapIngredientSteps->save();
                        }
                    }
                }
            }

            Recipe::where('recipe_id', $recipe->recipe_id)->update(['no_of_ingredients' => count($requestedFields['saved_ingredients'])]);
            
            $message = "Your recipe has been saved successfully";
            return response()->json(['success' => $this->successStatus,
                                        'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /** 
     * Create Image From Base64 string
     * 
     * Pamameters $img
     */ 
    public function createImage($img)
    {
        if(is_array($img)){
            return $img["id"];
        }else{
            $folderPath = storage_path('uploads/');
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            $uniqueName = uniqid() . '.'.$image_type;
            $file = $folderPath .''. $uniqueName;

            file_put_contents($file, $image_base64);

            $attachment = new Attachment;
            $attachment->attachment_url = 'uploads/'.$uniqueName;
            $attachment->attachment_type = $image_type;
            $attachment->attachment_type = $image_type;
            $attachment->save();
            return $attachment->id;
        }
        
    }

    /*
     * Get recipie detail
     * 
     */
    public function getRecipeDetail(Request $request, $recipeId)
    {
        try
        {
            $user = $this->user;
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $myRecipes = Recipe::with('image','meal','region')->with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('recipe_id', $recipeId)->first();

            if(!empty($myRecipes))
            {
                $userData = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->where('user_id', $myRecipes->user_id)->first();

                if($userData->role_id == 7 || $userData->role_id == 10)
                {
                    $name = ucwords(strtolower($userData->first_name)) . ' ' . ucwords(strtolower($userData->last_name));
                }
                elseif($userData->role_id == 9)
                {
                    $name = $userData->restaurant_name;
                }
                else
                {
                    $name = $userData->company_name;
                }
                $myRecipes->username = $name;
                $avgRating = RecipeReviewRating::where('recipe_id', $recipeId)->avg('rating');
                $totalReviews = RecipeReviewRating::where('recipe_id', $recipeId)->count();
                $getLatestReview = RecipeReviewRating::with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('recipe_id', $recipeId)->orderBy('recipe_review_rating_id', 'DESC')->first();
                

                if(!empty($getLatestReview))
                {
                    $userDataName = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->where('user_id', $getLatestReview->user_id)->first();
                    if($userDataName->role_id == 7 || $userDataName->role_id == 10)
                    {
                        $names = ucwords(strtolower($userDataName->first_name)) . ' ' . ucwords(strtolower($userDataName->last_name));
                    }
                    elseif($userDataName->role_id == 9)
                    {
                        $names = $userDataName->restaurant_name;
                    }
                    else
                    {
                        $names = $userDataName->company_name;
                    }
                    $getLatestReview->user->name = $names;
                }
                else
                {
                    //$getLatestReview->user->name = null;   
                }

                $oneStar = RecipeReviewRating::where('recipe_id', $myRecipes->recipe_id)->where('rating', 1)->count();
                $twoStar = RecipeReviewRating::where('recipe_id', $myRecipes->recipe_id)->where('rating', 2)->count();
                $threeStar = RecipeReviewRating::where('recipe_id', $myRecipes->recipe_id)->where('rating', 3)->count();
                $fourStar = RecipeReviewRating::where('recipe_id', $myRecipes->recipe_id)->where('rating', 4)->count();
                $fiveStar = RecipeReviewRating::where('recipe_id', $myRecipes->recipe_id)->where('rating', 5)->count();
                

                $myRecipes->user->name = $name;
                

                $isLikedRecipe = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $recipeId)->first();
                if(!empty($isLikedRecipe))
                {
                    $myRecipes->is_favourite = 1;
                }
                else
                {
                    $myRecipes->is_favourite = 0;   
                }

                $myRecipes->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $myRecipes->total_reviews = $totalReviews;
                $myRecipes->latest_review = $getLatestReview;

                $myRecipes->total_one_star = $oneStar;
                $myRecipes->total_two_star = $twoStar;
                $myRecipes->total_three_star = $threeStar;
                $myRecipes->total_four_star = $fourStar;
                $myRecipes->total_five_star = $fiveStar;

                $recipeUsedIngredients = RecipeSavedIngredient::with('ingredient','ingredient.image_id')->where('recipe_id', $recipeId)->get();
                $recipeUsedTools = RecipeSavedTool::with('tool','tool.image_id')->where('recipe_id', $recipeId)->get();

                $recipeUsedSteps = RecipeStep::where('recipe_id', $recipeId)->get();
                if(count($recipeUsedSteps) > 0)
                {
                    //$arrayValues = [];
                    //$arrayValuesTools = [];
                    foreach($recipeUsedSteps as $key => $recipeUsedStep)
                    {
                        //for saved ingredients for the steps
                        $mapIngredients = RecipeMapStepIngredient::where('recipe_id', $recipeId)->where('recipe_step_id', $recipeUsedStep->recipe_step_id)->get(); 

                        $recipeUsedIngredientss = RecipeSavedIngredient::with('ingredient','ingredient.image_id')->where('recipe_id', $recipeId)->get();
                        //return $mapIngredients;
                        if(count($mapIngredients) > 0)
                        {
                            
                            $arrayValues = $mapIngredients->pluck('recipe_saved_ingredient_id')->toArray();
                            foreach($recipeUsedIngredientss as $keyIng => $recipeUsedIngredient)
                            {
                                if(in_array($recipeUsedIngredient->ingredient_id, $arrayValues))
                                    $recipeUsedIngredientss[$keyIng]->is_selected = true;
                                else
                                    $recipeUsedIngredientss[$keyIng]->is_selected = false;

                            }
                        }
                        else
                        {
                            foreach($recipeUsedIngredientss as $keyIng => $recipeUsedIngredient)
                            {
                                $recipeUsedIngredientss[$keyIng]->is_selected = false;
                            }
                        }                   
                        
                        $recipeUsedSteps[$key]->step_ingredients = $recipeUsedIngredientss;

                        //for saved tools for the steps
                        $mapTools = RecipeMapStepTool::where('recipe_id', $recipeId)->where('recipe_step_id', $recipeUsedStep->recipe_step_id)->get(); 
                        //return $mapIngredients;

                        $recipeUsedToolss = RecipeSavedTool::with('tool','tool.image_id')->where('recipe_id', $recipeId)->get();
                        if(count($mapTools) > 0)
                        {
                            
                            $arrayValuesTools = $mapTools->pluck('recipe_saved_tool_id')->toArray();
                            foreach($recipeUsedToolss as $keyTool => $recipeUsedTool)
                            {
                                if(in_array($recipeUsedTool->tool_id, $arrayValuesTools))
                                    $recipeUsedToolss[$keyTool]->is_selected = true;
                                else
                                    $recipeUsedToolss[$keyTool]->is_selected = false;

                            }
                        }
                        else
                        {
                            foreach($recipeUsedToolss as $keyTool => $recipeUsedTool)
                            {
                                $recipeUsedToolss[$keyTool]->is_selected = false;
                            }
                        }

                        $recipeUsedSteps[$key]->step_tools = $recipeUsedToolss;
                    }
                }

                $youMightAlsoLikeData = Recipe::with('image','meal','region')->with('user:user_id,name,email,company_name,restaurant_name,role_id,avatar_id','user.avatar_id')->whereNotIn('recipe_id',[$myRecipes->recipe_id])->where('cousin_id', $myRecipes->cousin_id)->limit(6)->get();

                foreach($youMightAlsoLikeData as $lkey => $youMightAlsoLike)
                {
                    $userDetail = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->where('user_id', $youMightAlsoLike->user_id)->first();
                    if($userDetail->role_id == 7 || $userDetail->role_id == 10)
                    {
                        $userName = ucwords(strtolower($userDetail->first_name)) . ' ' . ucwords(strtolower($userDetail->last_name));
                    }
                    elseif($userDetail->role_id == 9)
                    {
                        $userName = $userDetail->restaurant_name;
                    }
                    else
                    {
                        $userName = $userDetail->company_name;
                    }
                    $youMightAlsoLikeData[$lkey]->user->name = $userName;
                    $avgRatings = RecipeReviewRating::where('recipe_id', $youMightAlsoLike->recipe_id)->avg('rating');
                    $youMightAlsoLikeData[$lkey]->avg_rating = number_format((float)$avgRatings, 1, '.', '');

                }
                
                return response()->json(['success' => $this->successStatus,
                                         'recipe' => $myRecipes,
                                         'used_ingredients' => $recipeUsedIngredients,
                                         'used_tools' => $recipeUsedTools,
                                         'steps' => $recipeUsedSteps,
                                         'you_might_also_like' => $youMightAlsoLikeData
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Get all my recipie
     * 
     */
    public function getMyRecipes()
    {
        try
        {
            $user = $this->user;

            $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->where('user_id', $user->user_id)->orderBy('recipe_id', 'DESC')->get();
            if(count($myRecipes) > 0)
            {
                foreach($myRecipes as $key => $myRecipe)
                {
                    $userDataName = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->where('user_id', $myRecipe->user_id)->first();
                    if($userDataName->role_id == 7 || $userDataName->role_id == 10)
                    {
                        $names = ucwords(strtolower($userDataName->first_name)) . ' ' . ucwords(strtolower($userDataName->last_name));
                    }
                    elseif($userDataName->role_id == 9)
                    {
                        $names = $userDataName->restaurant_name;
                    }
                    else
                    {
                        $names = $userDataName->company_name;
                    }

                    $isLikedRecipe = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $myRecipe->recipe_id)->first();
                    if(!empty($isLikedRecipe))
                    {
                        $myRecipes[$key]->is_favourite = 1;
                    }
                    else
                    {
                        $myRecipes[$key]->is_favourite = 0;   
                    }
                    $myRecipes[$key]->username = $names;
                    $avgRating = RecipeReviewRating::where('recipe_id', $myRecipe->recipe_id)->avg('rating');
                    $totalLikes = RecipeFavourite::where('recipe_id', $myRecipe->recipe_id)->count();

                    $myRecipes[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $myRecipes[$key]->total_likes = $totalLikes;
                    
                }

                return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($myRecipes),
                                        'data' => $myRecipes,
                                    ], $this->successStatus);
            }
            else
            {
                $message = "No regions found";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    public function createLink(){
        $user = $this->user;
        $myRecipes = Recipe::all();
        if(count($myRecipes) > 0)
        {
            foreach($myRecipes as $key => $myRecipe)
            {
                $parameters = [
                    'dynamicLinkInfo' => [
                        'domainUriPrefix' => 'https://devalysei.page.link',
                        'link' => 'https://dev.alysei.com/recipes/recipe-view/'. $myRecipe->recipe_id,
                        'androidInfo' => [
                            'androidPackageName' => 'com.alysei',
                        ],
                        "iosInfo" => [
                        'iosBundleId'=> 'com.app.Alysei',
                        'iosAppStoreId' => '1634783265',
                        ],
                    ],
                    'suffix' => ['option' => 'SHORT'],
                ];
    
                $link = $this->directLinkCon()->createDynamicLink($parameters);
                Recipe::where('recipe_id', $myRecipe->recipe_id)->update(['directLink' => $link]);
            }
        }

        return 'All Done';
    }


    /*
     * Favourite/unfavourite recipe
     * @Params $request
     */
    public function makeFavouriteOrUnfavourite(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_id' => 'required',
                'favourite_or_unfavourite' => 'required', // 1 for favourite 0 for unfavourite
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getRecipe = Recipe::where('recipe_id', $request->recipe_id)->first();
            if(!empty($getRecipe))
            {
                if($request->favourite_or_unfavourite == 1)
                {
                    $isLikedActivityPost = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->first();


                    if(!empty($isLikedActivityPost))
                    {
                        $message = "You have already added this recipe in your favourite list";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus); 
                    }
                    else
                    {
                        $activityLike = new RecipeFavourite;
                        $activityLike->user_id = $user->user_id;
                        $activityLike->recipe_id = $request->recipe_id;
                        $activityLike->save();

                        $getRecipe->favourite_count = $getRecipe->favourite_count + 1;
                        $getRecipe->save();

                        $message = "You added this recipe in your favourite list";
                        return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $getRecipe->favourite_count,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                    }
                }
                elseif($request->favourite_or_unfavourite == 0)
                {
                    $isLikedActivityPost = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->first();
                    if(!empty($isLikedActivityPost))
                    {
                        $isUnlikedActivityPost = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->delete();
                        if($isUnlikedActivityPost == 1)
                        {
                            $getRecipe->favourite_count = $getRecipe->favourite_count - 1;
                            $getRecipe->save();

                            $message = "You removed this recipe from your favourite list";
                            return response()->json(['success' => $this->successStatus,
                                                 'total_likes' => $getRecipe->favourite_count,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->successStatus);
                        }
                        else
                        {
                            $message = "You have to first add this recipe in your favourite list";
                            return response()->json(['success' => $this->exceptionStatus,
                                                 'message' => $this->translate('messages.'.$message,$message),
                                                ], $this->exceptionStatus);
                        }
                    }
                    else
                    {
                        $message = "You have not added this recipe in your favourite list";
                        return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                    }
                }
                else
                {
                    $message = "Invalid favourite/unfavourite type";
                    return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }
                
            }
            else
            {
                $message = "Invalid recipe id";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Get my favourite recipie
     * 
     */
    public function getMyFavouriteRecipes()
    {
        try
        {
            $user = $this->user;

            $myFavRecipes = RecipeFavourite::where('user_id', $user->user_id)->orderBy('recipe_favourite_id', 'DESC')->get();
            if(count($myFavRecipes) > 0)
            {
                $myFavRecipesId = $myFavRecipes->pluck('recipe_id');
                $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->whereIn('recipe_id', $myFavRecipesId)->orderBy('recipe_id', 'DESC')->get();

                if(count($myRecipes) > 0)
                {
                    foreach($myRecipes as $key => $myRecipe)
                    {
                        $userDataName = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->where('user_id', $myRecipe->user_id)->first();
                        if($userDataName->role_id == 7 || $userDataName->role_id == 10)
                        {
                            $names = ucwords(strtolower($userDataName->first_name)) . ' ' . ucwords(strtolower($userDataName->last_name));
                        }
                        elseif($userDataName->role_id == 9)
                        {
                            $names = $userDataName->restaurant_name;
                        }
                        else
                        {
                            $names = $userDataName->company_name;
                        }
                        $myRecipes[$key]->username = $names;
                        //$myRecipe[$key]->cousin->name = $this->translate('messages.'.$myRecipe->cousin->name,$myRecipe->cousin->name);
                        $avgRating = RecipeReviewRating::where('recipe_id', $myRecipe->recipe_id)->avg('rating');
                        $totalLikes = RecipeFavourite::where('recipe_id', $myRecipe->recipe_id)->count();

                        $myRecipes[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                        $myRecipes[$key]->total_likes = $totalLikes;
                    }
                    return response()->json(['success' => $this->successStatus,
                                        'count' =>  count($myRecipes),
                                        'data' => $myRecipes,
                                    ], $this->successStatus);
                }
                else
                {
                    $message = "No recipes found";
                    return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                }   
            }
            else
            {
                $message = "You have not liked any recipe";
                return response()->json(['success' => $this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Add ingredients
     * 
     */
    public function addIngredient(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'title' => 'required',
                'image_id' => 'required',
                'category' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $newIngredient = new RecipeIngredient;
            $newIngredient->image_id = $this->uploadImage($request->file('image_id'));
            $newIngredient->title_en = $request->title;
            $newIngredient->name = strtolower(str_replace(' ', '_', $request->title));
            $newIngredient->parent = $request->category;
            $newIngredient->save();

           
            $message = "ingredient added successfuly";
            return response()->json(['success' => $this->successStatus,
                                    'message' =>  $this->translate('messages.'.$message,$message),
                                    'data' => $newIngredient,
                                    ], $this->successStatus);
                       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Add ingredients
     * 
     */
    public function addTool(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'title' => 'required',
                'image_id' => 'required',
                'category' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $newIngredient = new RecipeTool;
            $newIngredient->image_id = $this->uploadImage($request->file('image_id'));
            $newIngredient->title = $request->title;
            $newIngredient->name = strtolower(str_replace(' ', '_', $request->title));
            $newIngredient->parent = $request->category;
            $newIngredient->save();

           
            $message = "Tool added successfuly";
            return response()->json(['success' => $this->successStatus,
                                    'message' =>  $this->translate('messages.'.$message,$message),
                                    'data' => $newIngredient,
                                    ], $this->successStatus);
                       
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Make a Review on recipe
     * @Params $request
     */
    public function doReview(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_id' => 'required',
                'rating' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $isRated = RecipeReviewRating::where('user_id', $user->user_id)->where('recipe_id', $request->recipe_id)->first();
            if(!empty($isRated))
            {
                $message = "You have already done a review on this recipe";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            else
            {
                $review = new RecipeReviewRating;
                $review->user_id = $user->user_id;
                $review->recipe_id = $request->recipe_id;
                $review->rating = $request->rating;
                $review->review = $request->review;
                $review->save();

                $message = "Your rating has been done";
                return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.$message,$message),
                                            'data' => $review,
                                         ], $this->successStatus);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Update a Review on recipe
     * @Params $request
     */
    public function updateReview(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_review_rating_id' => 'required',
                'rating' => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $isRated = RecipeReviewRating::where('user_id', $user->user_id)->where('recipe_review_rating_id', $request->recipe_review_rating_id)->first();
            if(!empty($isRated))
            {
                /*$isRated->rating = $request->rating;
                $isRated->review = $request->review;
                $isRated->save();*/
                RecipeReviewRating::where('recipe_review_rating_id', $request->recipe_review_rating_id)->update(['rating'=>$request->rating]);
                if(!empty($request->review))
                    RecipeReviewRating::where('recipe_review_rating_id', $request->recipe_review_rating_id)->update(['review'=>$request->review]);
                $isRated = RecipeReviewRating::where('user_id', $user->user_id)->where('recipe_review_rating_id', $request->recipe_review_rating_id)->first();
                $message = "Your rating has been updated";
                return response()->json(['success' => $this->successStatus,
                                            'message' => $this->translate('messages.'.$message,$message),
                                            'data' => $isRated,
                                         ], $this->successStatus);
            }
            else
            {
                $message = "Something went wrong";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
                
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * get all ratings
     * @Params $request
     */
    public function getReviews(Request $request)
    {
        try
        {
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'recipe_id'   => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }

            $getAllRatings = RecipeReviewRating::with('user:user_id,name,email,company_name,first_name,last_name,restaurant_name,role_id,avatar_id','user.avatar_id')->where('recipe_id', $request->recipe_id)->orderBy('recipe_review_rating_id', 'DESC')->get()->toArray();
            if(count($getAllRatings) > 0)
            {
                foreach($getAllRatings as $key => $rating)
                {
                    $userDataName = User::select('user_id','name','email','first_name','last_name','company_name','restaurant_name','role_id','avatar_id')->where('user_id', $rating['user_id'])->first();
                    if($userDataName->role_id == 7 || $userDataName->role_id == 10)
                    {
                        $names = ucwords(strtolower($userDataName->first_name)) . ' ' . ucwords(strtolower($userDataName->last_name));
                    }
                    elseif($userDataName->role_id == 9)
                    {
                        $names = $userDataName->restaurant_name;
                    }
                    else
                    {
                        $names = $userDataName->company_name;
                    }
                    $getAllRatings[$key]['user']['name'] = $names;

                    if($rating['user_id'] == $user->user_id)
                    {
                        $new_value = $getAllRatings[$key];
                        unset($getAllRatings[$key]);
                        array_unshift($getAllRatings, $new_value);    
                    }
                }
                $isRated = RecipeReviewRating::where('recipe_id', $request->recipe_id)->where('user_id', $user->user_id)->first();
                return response()->json(['success' => $this->successStatus,
                                            'is_rated' => (!empty($isRated) ? 1 : 0),
                                            'data' => $getAllRatings,
                                         ], $this->successStatus);
            }
            else
            {
                $message = "No review found on this recipe";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
            
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * Save preferences
     * @Params $request
     */
    public function savePreferences(Request $request)
    {
        try
        {
            $user = $this->user;
            if(!empty($request->params))
            {
                $getPreferences = PreferenceMapUser::where('user_id', $user->user_id)->get();
                if(count($getPreferences) > 0)
                {
                    PreferenceMapUser::where('user_id', $user->user_id)->delete();
                    PreferenceMapCousin::where('user_id', $user->user_id)->delete();
                    PreferenceMapIntolerance::where('user_id', $user->user_id)->delete();
                    PreferenceMapDiet::where('user_id', $user->user_id)->delete();
                    PreferenceMapIngredient::where('user_id', $user->user_id)->delete();
                    PreferenceMapCookingSkill::where('user_id', $user->user_id)->delete();
                }
                foreach($request->params as $key => $preferences)
                {
                    $newPreference = new PreferenceMapUser;
                    $newPreference->user_id = $user->user_id;
                    $newPreference->preference = $preferences['preference'];
                    $newPreference->save();
                        
                    if($preferences['preference'] == 1)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapCousin;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->cousin_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }
                    elseif($preferences['preference'] == 2)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapIntolerance;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->intolerance_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    } 
                    elseif($preferences['preference'] == 3)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapDiet;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->diet_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }  
                    elseif($preferences['preference'] == 4)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapIngredient;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->ingredient_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }    
                    elseif($preferences['preference'] == 5)
                    {
                        foreach($preferences['id'] as $preferenceId)
                        {
                            $mapPreference = new PreferenceMapCookingSkill;
                            $mapPreference->user_id = $user->user_id;
                            $mapPreference->preference_id = $newPreference->id;
                            $mapPreference->cooking_skill_id = $preferenceId;
                            $mapPreference->save();
                        }
                        
                    }  
                    
                } 
            }
            $message = "Preferences has been saved successfully";
            return response()->json(['success' => $this->successStatus,
                                    'message' => $this->translate('messages.'.$message,$message),
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * get Saved preferences
     * @Params $request
     */
    public function getPreferences(Request $request)
    {
        try
        {
            $user = $this->user;
            
            $allPreferences = Preference::get();
            $lang = 'en';
            $language = $request->header('locale');
            if(!empty($language)){
                $lang = $language;
            }
            if(count($allPreferences) > 0)
            {
                //return $allPreferences;
                foreach($allPreferences as $key => $allPreference)
                {
                    if($allPreference->preference_id == 1)
                    {
                        $cousins = Cousin::with('image_id')->where('status', 1)->get();
                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();
                        $arrayValues1 = [];
                        foreach($cousins as $keys => $cousin)
                        {
                            $cousins[$key]->name = $this->translate('messages.'.$cousin->name,$cousin->name);
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $cousinsPreference = PreferenceMapCousin::where('preference_id', $myPreferences->id)->get();
                                foreach($cousinsPreference as $cousinsPref)
                                {
                                    array_push($arrayValues1, $cousinsPref->cousin_id);
                                }
                                //$cousinsPreference = $cousinsPreference->pluck('cousin_id');
                                if(in_array($cousin->cousin_id, $arrayValues1))
                                    $cousins[$keys]->is_selected = true;
                                else
                                    $cousins[$keys]->is_selected = false;
                            }
                            else
                            {
                                $cousins[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $cousins;
                        $allPreferences[$key]->name = $allPreferences[$key]["name_".$lang];
                    }
                    elseif($allPreference->preference_id == 2)
                    {
                        $intolerances = RecipeFoodIntolerance::with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();
                        $arrayValues2 = [];
                        foreach($intolerances as $keys => $intolerance)
                        {
                            $intolerances[$keys]->name = $this->translate('messages.'.$intolerance->name,$intolerance->name);
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $intolerancePreference = PreferenceMapIntolerance::where('preference_id', $myPreferences->id)->get();
                                foreach($intolerancePreference as $intolerancePref)
                                {
                                    array_push($arrayValues2, $intolerancePref->intolerance_id);
                                }
                                if(in_array($intolerance->recipe_food_intolerance_id, $arrayValues2))
                                    $intolerances[$keys]->is_selected = true;
                                else
                                    $intolerances[$keys]->is_selected = false;
                            }
                            else
                            {
                                $intolerances[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $intolerances;
                        $allPreferences[$key]->name = $allPreferences[$key]["name_".$lang];
                    }
                    elseif($allPreference->preference_id == 3)
                    {
                        $diets = RecipeDiet::select('recipe_diet_id','name_'.$lang.' as name','image_id','created_at','updated_at','featured','priority')->with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();

                        $arrayValues3 = [];
                        foreach($diets as $keys => $diet)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $dietPreference = PreferenceMapDiet::where('preference_id', $myPreferences->id)->get();
                                foreach($dietPreference as $dietPref)
                                {
                                    array_push($arrayValues3, $dietPref->diet_id);
                                }
                                if(in_array($diet->recipe_diet_id, $arrayValues3))
                                    $diets[$keys]->is_selected = true;
                                else
                                    $diets[$keys]->is_selected = false;
                            }
                            else
                            {
                                $diets[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $diets;
                        $allPreferences[$key]->name = $allPreferences[$key]["name_".$lang];
                    }
                    elseif($allPreference->preference_id == 4)
                    {
                        $ingredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','image_id','created_at','updated_at','featured','priority')->with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();

                        $arrayValues4 = [];
                        foreach($ingredients as $keys => $ingredient)
                        {
                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $ingredientPreference = PreferenceMapIngredient::where('preference_id', $myPreferences->id)->get();
                                foreach($ingredientPreference as $ingredientPref)
                                {
                                    array_push($arrayValues4, $ingredientPref->ingredient_id);
                                }
                                if(in_array($ingredient->recipe_ingredient_id, $arrayValues4))
                                    $ingredients[$keys]->is_selected = true;
                                else
                                    $ingredients[$keys]->is_selected = false;
                            }
                            else
                            {
                                $ingredients[$keys]->is_selected = false;
                            }
                        }

                        $allPreferences[$key]->maps = $ingredients;
                        $allPreferences[$key]->name = $allPreferences[$key]["name_".$lang];
                    }
                    elseif($allPreference->preference_id == 5)
                    {
                        $cookingSkills = RecipeCookingSkill::with('image_id')->get();

                        $myPreferences = PreferenceMapUser::where('user_id', $user->user_id)->where('preference', $allPreference->preference_id)->first();

                        $arrayValues5 = [];
                        foreach($cookingSkills as $keys => $cookingSkill)
                        {

                            if(!empty($myPreferences) && $myPreferences->user_id == $user->user_id)
                            {
                                $cookingPreference = PreferenceMapCookingSkill::where('preference_id', $myPreferences->id)->get();
                                foreach($cookingPreference as $cookingPref)
                                {
                                    array_push($arrayValues5, $cookingPref->cooking_skill_id);
                                }
                                if(in_array($cookingSkill->recipe_cooking_skill_id, $arrayValues5))
                                    $cookingSkills[$keys]->is_selected = true;
                                else
                                    $cookingSkills[$keys]->is_selected = false;
                            }
                            else
                            {
                                $cookingSkills[$keys]->is_selected = false;
                            }
                            $cookingSkills[$keys]->name = $this->translate('messages.'.$cookingSkill->name,$cookingSkill->name);
                        }

                        $allPreferences[$key]->maps = $cookingSkills;
                        $allPreferences[$key]->name = $allPreferences[$key]["name_".$lang];
                    } 
                                      
                }
            }
            
            return response()->json(['success' => $this->successStatus,
                                    'data' => $allPreferences,
                                    ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
    /*
     * Get home for recipie
     * 
     */
    public function getHomeScreen(Request $request)
    {
        try
        { 
            $user = $this->user;
            $recipeArray = array();
            $quickRecipeArray = array();
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            /*$categories = RecipeCategory::with('image_id')->where('status', '1')->get();
            
            foreach($categories as $key => $category)
            {
                $categories[$key]->name = $this->translate('messages.'.$category->name,$category->name);
            }*/
            $meals = RecipeMeal::select('recipe_meal_id','name_'.$lang.' as name','image_id','featured','priority','created_at','updated_at')->with('image_id')->orderBy('name', 'asc')->get();
            
            // foreach($meals as $key => $meal)
            // {
            //     $meals[$key]->name = $this->translate('messages.'.$meal->name,$meal->name);
            // }

            $parentIngredients = RecipeIngredient::select('recipe_ingredient_id','title_'.$lang.' as title','name','image_id','parent','featured','priority','created_at','updated_at')->with('image_id')->where('parent','=', 0)->orderBy('title', 'asc')->get();
            // if(count($parentIngredients) > 0)
            // {
            //     foreach($parentIngredients as $key => $parentIngredient)
            //     {
            //         $parentIngredients[$key]->title = $this->translate('messages.'.$parentIngredient->title,$parentIngredient->title);                    
            //     }
            // }

            $regions = RecipeRegion::select('recipe_region_id','cousin_id','name_'.$lang.' as name','image_id','featured','priority','created_at','updated_at')->with('image_id')->orderBy('name', 'asc')->get();
            
            // foreach($regions as $key => $region)
            // {
            //     $regions[$key]->name = $this->translate('messages.'.$region->name,$region->name);
            // }

            //trending

            $favouriteRecipes = DB::select(DB::raw("select recipe_id from recipe_favourites GROUP BY recipe_id ORDER BY count(*) DESC LIMIT 8"));
            foreach($favouriteRecipes as $favouriteRecipe)
            {
                array_push($recipeArray, $favouriteRecipe->recipe_id);
            }

            $myRecipes = Recipe::with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->whereIn('recipe_id', $recipeArray)->orderBy('recipe_id', 'DESC')->get();
            foreach($myRecipes as $key => $recipe)
            {
                $recipeOwner = User::where('user_id', $recipe->user_id)->first();
                if($recipeOwner->role_id == 7 || $recipeOwner->role_id == 10)
                {
                    $name = ucwords(strtolower($recipeOwner->first_name)) . ' ' . ucwords(strtolower($recipeOwner->last_name));
                }
                elseif($recipeOwner->role_id == 9)
                {
                    $name = $recipeOwner->restaurant_name;
                }
                else
                {
                    $name = $recipeOwner->company_name;
                }

                $isLikedRecipe = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $recipe->recipe_id)->first();
                if(!empty($isLikedRecipe))
                {
                    $myRecipes[$key]->is_favourite = 1;
                }
                else
                {
                    $myRecipes[$key]->is_favourite = 0;   
                }
                
                $avgRating = RecipeReviewRating::where('recipe_id', $recipe->recipe_id)->avg('rating');
                $totalLikes = RecipeFavourite::where('recipe_id', $recipe->recipe_id)->count();

                $myRecipes[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                $myRecipes[$key]->total_likes = $totalLikes;
                $myRecipes[$key]->username = $name;
            }

            //quick easy
            //$easyRecipes = DB::select(DB::raw("select recipe_id from `recipe_steps` where deleted_at is null GROUP BY recipe_id ORDER BY count(recipe_step_id) ASC LIMIT 8"));


            $easyRecipes = Recipe::leftJoin('recipe_steps', 'recipes.recipe_id', '=', 'recipe_steps.recipe_id')->select('recipes.recipe_id',DB::raw('COUNT(recipe_steps.recipe_step_id) as total_steps'), DB::raw("CONCAT(recipes.hours,':',recipes.minutes) as recipe_time"))->whereNull('recipe_steps.deleted_at')->groupBy('recipe_steps.recipe_id')->orderBy("total_steps","ASC")->orderBy("recipe_time","ASC")->limit(8)->get();
            
            foreach($easyRecipes as $easyRecipe)
            {
                array_push($quickRecipeArray, $easyRecipe->recipe_id);
            }

            
            $quickEasyRecipes = Recipe::select('*', DB::raw("CONCAT(recipes.hours,':',recipes.minutes) as recipe_time"))->with('image','meal','course','cousin','region','diet','intolerance','cookingskill')->whereIn('recipe_id', $quickRecipeArray)->orderBy('recipe_time', 'ASC')->orderBy('recipe_id', 'DESC')->get();

            //return $quickEasyRecipes;
            foreach($quickEasyRecipes as $keyRecipe => $quickEasyRecipe)
            {
                $quickRecipeOwner = User::where('user_id', $quickEasyRecipe->user_id)->first();

                if($quickRecipeOwner){
                    
                    if($quickRecipeOwner->role_id == 7 || $quickRecipeOwner->role_id == 10)
                    {
                        $recipeOwnerName = ucwords(strtolower($quickRecipeOwner->first_name)) . ' ' . ucwords(strtolower($quickRecipeOwner->last_name));
                    }
                    elseif($quickRecipeOwner->role_id == 9)
                    {
                        $recipeOwnerName = $quickRecipeOwner->restaurant_name;
                    }
                    else
                    {
                        $recipeOwnerName = $quickRecipeOwner->company_name;
                    }

                    $isLikedQuickRecipe = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $quickEasyRecipe->recipe_id)->first();
                    if(!empty($isLikedQuickRecipe))
                    {
                        $quickEasyRecipes[$keyRecipe]->is_favourite = 1;
                    }
                    else
                    {
                        $quickEasyRecipes[$keyRecipe]->is_favourite = 0;   
                    }

                    $avgRatingOfQuickRecipe = RecipeReviewRating::where('recipe_id', $quickEasyRecipe->recipe_id)->avg('rating');
                    $totalLikesOfQuickRecipe = RecipeFavourite::where('recipe_id', $quickEasyRecipe->recipe_id)->count();

                    $quickEasyRecipes[$keyRecipe]->avg_rating = number_format((float)$avgRatingOfQuickRecipe, 1, '.', '');
                    $quickEasyRecipes[$keyRecipe]->total_likes = $totalLikesOfQuickRecipe;
                    $quickEasyRecipes[$keyRecipe]->username = $recipeOwnerName;
                }
            }


            $data = ['ingredients' => $parentIngredients, 'meals' => $meals, 'regions' => $regions, 'trending_recipes' => $myRecipes, 'quick_easy' => $quickEasyRecipes];


            return response()->json(['success' => $this->successStatus,
                                    'data' => $data,
                                ], $this->successStatus);
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
    

    /*
     * Search Recipe
     * @Params $request
     */
    public function searchRecipe(Request $request)
    {
        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            $condition = '';
            $user = $this->user;
            $validator = Validator::make($request->all(), [ 
                'keyword'   => 'required',
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            if(!empty($request->cook_time) && $request->cook_time > 0)
            {
                $isSearch = 1;
                if($request->cook_time == 1 || $request->cook_time == 2)
                {
                    $request->cook_time = $request->cook_time * 60;
                }

                if($condition != '')
                    $condition .=" and recipes.preparation_time_mins <= ".$request->cook_time;
                else
                    $condition .=" recipes.preparation_time_mins <= ".$request->cook_time;
                    
            }
            
            if(!empty($request->meal_type))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.meal_id = ".$request->meal_type;
                else
                $condition .="recipes.meal_id = ".$request->meal_type;
                
            }
            
            if(!empty($request->cousin_id))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.cousin_id = ".$request->cousin_id;
                else
                $condition .="recipes.cousin_id = ".$request->cousin_id;
                
            }

            if(!empty($request->region_id))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.region_id = ".$request->region_id;
                else
                $condition .="recipes.region_id = ".$request->region_id;
                
            }

            if(!empty($request->no_of_ingredients))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.no_of_ingredients < ".$request->no_of_ingredients;
                else
                $condition .="recipes.no_of_ingredients < ".$request->no_of_ingredients;
                
            }

            //$recipes = Recipe::with('image','meal')->where('name', 'LIKE', '%'.$request->keyword.'%')
            $getRecipeByIngId = RecipeIngredient::where('title_en', 'LIKE', '%'.$request->keyword.'%')->get();

            if(count($getRecipeByIngId) > 0)
            {   
                $ingIds = $getRecipeByIngId->pluck('recipe_ingredient_id')->toArray();
                $getRecipeChildIngId = RecipeIngredient::whereIn('parent', $ingIds)->get();
                $childIng = $getRecipeChildIngId->pluck('recipe_ingredient_id')->toArray();
                $getRecipeSavedIngId = RecipeSavedIngredient::whereIn('ingredient_id', $childIng)->get();
                if(count($getRecipeSavedIngId) > 0)
                {
                    $recipesId = $getRecipeSavedIngId->pluck('recipe_id')->toArray();
                }
                else
                {
                    $recipesId = [];
                }
            }
            else
            {
                $recipesId = [];
            }
            
            if(count($recipesId) > 0)
            {
                if($condition != '')
                {
                    $recipes = DB::table('recipes')
                    ->join('recipe_meals', 'recipe_meals.recipe_meal_id', '=', 'recipes.meal_id')
                    ->join('attachments', 'attachments.id', '=', 'recipes.image_id')
                    ->join('recipe_regions', 'recipe_regions.recipe_region_id', '=', 'recipes.region_id')
                    ->select('recipes.name as recipe_name','recipes.*', 'recipe_meals.name_'.$lang.' as meal_name', 'recipe_meals.*','attachments.attachment_url','attachments.base_url')
                    ->whereNull('deleted_at')
                    ->whereRaw('('.$condition.')')
                    ->where(function ($query) use ($request, $recipesId) {
                        $query->where('recipes.name', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_meals.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_regions.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhereIn('recipes.recipe_id', $recipesId);

                    })
                    ->paginate(10);
                }
                else
                {
                    $recipes = DB::table('recipes')
                    ->join('recipe_meals', 'recipe_meals.recipe_meal_id', '=', 'recipes.meal_id')
                    ->join('attachments', 'attachments.id', '=', 'recipes.image_id')
                    ->join('recipe_regions', 'recipe_regions.recipe_region_id', '=', 'recipes.region_id')
                    ->select('recipes.name as recipe_name','recipes.*', 'recipe_meals.name_'.$lang.' as meal_name', 'recipe_meals.*','attachments.attachment_url','attachments.base_url')
                    ->whereNull('deleted_at')
                    ->where(function ($query) use ($request, $recipesId) {
                        $query->where('recipes.name', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_meals.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_regions.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhereIn('recipes.recipe_id', $recipesId);

                    })
                    ->paginate(10);
                }
                
            }
            else
            {
                if($condition != '')
                {
                    $recipes = DB::table('recipes')
                    ->join('recipe_meals', 'recipe_meals.recipe_meal_id', '=', 'recipes.meal_id')
                    ->join('attachments', 'attachments.id', '=', 'recipes.image_id')
                    ->join('recipe_regions', 'recipe_regions.recipe_region_id', '=', 'recipes.region_id')
                    ->select('recipes.name as recipe_name','recipes.*', 'recipe_meals.name_'.$lang.' as meal_name', 'recipe_meals.*','attachments.attachment_url','attachments.base_url')
                    ->whereRaw('('.$condition.')')
                    ->whereNull('deleted_at')
                    ->where(function ($query) use ($request) {
                        $query->where('recipes.name', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_meals.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_meals.name_it', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_regions.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_regions.name_it', 'LIKE', '%'.$request->keyword.'%');
                    })
                    ->paginate(10);
                }
                else
                {
                    $recipes = DB::table('recipes')
                    ->join('recipe_meals', 'recipe_meals.recipe_meal_id', '=', 'recipes.meal_id')
                    ->join('attachments', 'attachments.id', '=', 'recipes.image_id')
                    ->join('recipe_regions', 'recipe_regions.recipe_region_id', '=', 'recipes.region_id')
                    ->select('recipes.name as recipe_name','recipes.*', 'recipe_meals.name_'.$lang.' as meal_name', 'recipe_meals.*','attachments.attachment_url','attachments.base_url')
                    ->whereNull('deleted_at')
                    ->where(function ($query) use ($request) {
                        $query->where('recipes.name', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_meals.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_meals.name_it', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_regions.name_en', 'LIKE', '%'.$request->keyword.'%')
                              ->orWhere('recipe_regions.name_it', 'LIKE', '%'.$request->keyword.'%');
                    })
                    ->paginate(10);

                }
                
            }

            /*if(!empty($request->region_id) && empty($request->cook_time) && empty($request->meal_type) && empty($request->cousin_id) && empty($request->no_of_ingredients))
            {
                $recipes = DB::table('recipes')
                ->join('recipe_meals', 'recipe_meals.recipe_meal_id', '=', 'recipes.meal_id')
                ->join('attachments', 'attachments.id', '=', 'recipes.image_id')
                ->select('recipes.name as recipe_name','recipes.*', 'recipe_meals.name as meal_name', 'recipe_meals.*','attachments.attachment_url')
                ->where('recipes.region_id', '=', $request->region_id)
                ->paginate(10);
            }*/
   
            
            if(count($recipes) > 0)
            {
                foreach($recipes as $key => $recipe)
                {
                    $recipeOwner = User::where('user_id', $recipe->user_id)->first();
                    if($recipeOwner->role_id == 7 || $recipeOwner->role_id == 10)
                    {
                        $name = ucwords(strtolower($recipeOwner->first_name)) . ' ' . ucwords(strtolower($recipeOwner->last_name));
                    }
                    elseif($recipeOwner->role_id == 9)
                    {
                        $name = $recipeOwner->restaurant_name;
                    }
                    else
                    {
                        $name = $recipeOwner->company_name;
                    }

                    $isLikedRecipe = RecipeFavourite::where('user_id', $user->user_id)->where('recipe_id', $recipe->recipe_id)->first();
                    if(!empty($isLikedRecipe))
                    {
                        $recipes[$key]->is_favourite = 1;
                    }
                    else
                    {
                        $recipes[$key]->is_favourite = 0;   
                    }
                    
                    $avgRating = RecipeReviewRating::where('recipe_id', $recipe->recipe_id)->avg('rating');
                    $totalLikes = RecipeFavourite::where('recipe_id', $recipe->recipe_id)->count();

                    $recipes[$key]->avg_rating = number_format((float)$avgRating, 1, '.', '');
                    $recipes[$key]->total_likes = $totalLikes;
                    $recipes[$key]->username = $name;
                }
                return response()->json(['success' => $this->successStatus,
                                        'count' => count($recipes),
                                        'data' => $recipes
                                ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /*
     * filter recipe
     * @Params $request
     */
    public function filterRecipe(Request $request)
    {
        try
        {
            $user = $this->user;
            $recipeArray = array();
            $condition = '';
            $isSearch = 0;
            
            if(!empty($request->cook_time))
            {
                $isSearch = 1;
                if($request->cook_time == 1 || $request->cook_time == 2)
                {
                    if($condition != '')
                    $condition .=" and recipes.hours = ".$request->cook_time;
                    else
                    $condition .="recipes.hours = ".$request->cook_time;
                }
                else
                {
                    if($condition != '')
                    $condition .=" and recipes.minutes = ".$request->cook_time;
                    else
                    $condition .="recipes.minutes = ".$request->cook_time;
                }
            }
            
            if(!empty($request->meal_type))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.meal_id = ".$request->meal_type;
                else
                $condition .="recipes.meal_id = ".$request->meal_type;
                
            }
            
            
            if(!empty($request->cousin_id))
            {
                $isSearch = 1;
                
                if($condition != '')
                $condition .=" and recipes.cousin_id = ".$request->cousin_id;
                else
                $condition .="recipes.cousin_id = ".$request->cousin_id;
                
            }

            if(empty($request->no_of_ingredients) || empty($request->child_ingredient))
            {
                if(!empty($request->no_of_ingredients))
                {
                    $isSearch = 1;
                    $ingredientArray = array();
                    $recipeByIngredients = DB::select(DB::raw("select `recipe_id` from `recipe_saved_ingredients` group by recipe_id having count(ingredient_id) < ".$request->no_of_ingredients));
                    if(count($recipeByIngredients) > 0)
                    {
                        foreach($recipeByIngredients as $recipeByIngredient)
                        {
                            array_push($ingredientArray, $recipeByIngredient->recipe_id);
                        }
                        $ingredientValues = join(",", $ingredientArray);

                        if($condition != '')
                        $condition .=" and recipes.recipe_id in(".$ingredientValues.")";
                        else
                        $condition .="recipes.recipe_id in(".$ingredientValues.")";
                    }
                    else
                    {
                        $ingredientValues = '';
                    }
                }
                if(!empty($request->child_ingredient))
                {
                    $isSearch = 1;
                    
                    $ingredientArrays = array();
                    $recipeByIngredient = DB::select(DB::raw("select `recipe_id` from `recipe_saved_ingredients` where ingredient_id in(".$request->child_ingredient.") group by recipe_id"));
                    
                    if(!empty($recipeByIngredient))
                    {
                        foreach($recipeByIngredient as $recipeByIngrediente)
                        {
                            array_push($ingredientArrays, $recipeByIngrediente->recipe_id);
                        }
                        $ingredientValue = join(",", $ingredientArrays);

                        if($condition != '')
                        $condition .=" and recipes.recipe_id in(".$ingredientValue.")";
                        else
                        $condition .="recipes.recipe_id in(".$ingredientValue.")";
                    }
                    else
                    {
                        $ingredientValue = '';
                    }
                    
                }
            }
            else
            {
                if(!empty($ingredientValues) && !empty($ingredientValue))
                {
                    $result = implode("," , array_unique(array_merge(explode(",",$ingredientValues),explode(",", $ingredientValue))));
                    if($condition != '')
                    $condition .=" and recipes.recipe_id in(".$result.")";
                    else
                    $condition .="recipes.recipe_id in(".$result.")";
                }
                
            }

            
            if($condition != '')
            {
                $recipes = Recipe::with('image')->whereRaw('('.$condition.')')->where('name', 'LIKE', '%'.$request->keyword.'%')->paginate(10);    
                return response()->json(['success' => $this->successStatus,
                                        'data' => $recipes
                                ], $this->successStatus);
            }
            else
            {
                $message = "No recipe found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }


    /*
     * Validate Data
     * @Params $requestedfields
    */

    public function validateData($requestedFields){
        $rules = [];
        foreach ($requestedFields as $key => $field) 
        {
            if($key == 'name')
            {
                $rules[$key] = 'required|max:190';
            }
            elseif($key == 'meal_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'course_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'minutes')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'serving')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'cousin_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'region_id')
            {
                $rules[$key] = 'required';
            }
            /*elseif($key == 'intolerance_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'diet_id')
            {
                $rules[$key] = 'required';
            }*/
            elseif($key == 'cooking_skill_id')
            {
                $rules[$key] = 'required';
            }
            elseif($key == 'image_id')
            {
                $rules[$key] = 'required';
            }

            //Categories
            elseif($key == 'category_id')
            {
                $rules[$key] = 'required';
            }


            
        }

        return $rules;

    }

    // Get All Recipe Quantity
    public function getRecipeQuantity(Request $request){

        try
        {
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }

            $recipeQuantities = UnitQuantity::select('id','name_'.$lang.' as name')->get();
            return response()->json(['success' => $this->successStatus,
                                        'data' => $recipeQuantities
                                ], $this->successStatus);


        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    

   
}
