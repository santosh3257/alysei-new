<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeIngredient; 
use App\Http\Traits\UploadImageTrait;
use Validator;

class IngredientsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $ingredients = RecipeIngredient::with('attachment')->paginate('10');
        return view('recipe::ingredients.index',compact('ingredients'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $ingredients = RecipeIngredient::select("title_en","recipe_ingredient_id")->where('parent',0)->get();
        return view('recipe::ingredients.create',compact('ingredients'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'name_en' => 'required',
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newIngredient = new RecipeIngredient;
            $newIngredient->image_id = $this->uploadFrontImage($request->file('image'));
            $newIngredient->title_en = $request->name_en;
            $newIngredient->title_it = $request->name_it;
            $newIngredient->name = strtolower(str_replace(' ', '_', $request->name_en));
            $newIngredient->parent = isset($request->parent) ? 0 : $request->parent_id;
            $newIngredient->featured = isset($request->featured) ? 1 : 0;
            $newIngredient->priority = $request->priority;
            $newIngredient->save();

            $message = "ingredient added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
            //return redirect()->back()->with('error', "Something went wrong");   
            //return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('recipe::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $ingredient = RecipeIngredient::where('recipe_ingredient_id',$id)->with("attachment")->first();
        $parentIngredients = RecipeIngredient::select("title_en","recipe_ingredient_id")->where('parent',0)->get();
        $displayNone = "style=display:none";
        return view('recipe::ingredients.edit',compact('ingredient','parentIngredients','displayNone','id'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'name_en' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];

            if($request->file('image')){
                $updatedData['image_id'] = $this->uploadFrontImage($request->file('image'));    
            }

            $updatedData['title_en'] = $request->name_en;
            $updatedData['title_it'] = $request->name_it;
            $updatedData['name'] = strtolower(str_replace(' ', '_', $request->name_en));
            $updatedData['parent'] = isset($request->parent) ? 0 : $request->parent_id;
            $updatedData['featured'] = isset($request->featured) ? 1 : 0;
            $updatedData['priority'] = $request->priority;

            RecipeIngredient::where('recipe_ingredient_id',$id)->update($updatedData);

            $message = "ingredient updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
            //return redirect()->back()->with('error', "Something went wrong");   
            //return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        
        $recipeIngredient = RecipeIngredient::where('recipe_ingredient_id',$id)->delete();
        if($recipeIngredient){
            //$recipeIngredient->delete();
            $message = "Recipe ingredient deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted it.";
            return redirect()->back()->with('error', $message);
        }
    }
}
