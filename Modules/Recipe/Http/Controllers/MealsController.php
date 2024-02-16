<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeMeal; 
use App\Http\Traits\UploadImageTrait;
use Validator;

class MealsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $meals = RecipeMeal::with('attachment')->paginate('10');
        return view('recipe::meals.index',compact('meals'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('recipe::meals.create');
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

            $newMeal = new RecipeMeal;
            $newMeal->image_id = $this->uploadFrontImage($request->file('image'));
            $newMeal->name_en = $request->name_en;
            $newMeal->name_it = $request->name_it;
            $newMeal->featured = isset($request->featured) ? 1 : 0;
            $newMeal->priority = $request->priority;
            $newMeal->save();

            $message = "Meal added successfuly";
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
        $meal = RecipeMeal::where('recipe_meal_id',$id)->with("attachment")->first();
        return view('recipe::meals.edit',compact('meal','id'));
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

            $updatedData['name_en'] = $request->name_en;
            $updatedData['name_it'] = $request->name_it;
            $updatedData['featured'] = isset($request->featured) ? 1 : 0;
            $updatedData['priority'] = $request->priority;
            
            RecipeMeal::where('recipe_meal_id',$id)->update($updatedData);

            $message = "Meal updated successfuly";
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
        $recipeMeal = RecipeMeal::where('recipe_meal_id',$id)->delete();
        if($recipeMeal){
            //$recipeIngredient->delete();
            $message = "Recipe meal deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted it.";
            return redirect()->back()->with('error', $message);
        }
    }
}
