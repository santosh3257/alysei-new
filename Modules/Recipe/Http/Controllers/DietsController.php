<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeDiet; 
use App\Http\Traits\UploadImageTrait;
use Validator;

class DietsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $diets = RecipeDiet::with('attachment')->paginate('10');
        return view('recipe::diets.index',compact('diets'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('recipe::diets.create');
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

            $newDiet = new RecipeDiet;
            $newDiet->image_id = $this->uploadImage($request->file('image'));
            $newDiet->name_en = $request->name_en;
            $newDiet->featured = isset($request->featured) ? 1 : 0;
            $newDiet->priority = $request->priority;
            $newDiet->save();

            $message = "Diet added successfuly";
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
        $diet = RecipeDiet::where('recipe_diet_id',$id)->with("attachment")->first();
        return view('recipe::diets.edit',compact('diet','id'));
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
                $updatedData['image_id'] = $this->uploadImage($request->file('image'));    
            }

            $updatedData['name_en'] = $request->name_en;
            $updatedData['name_it'] = $request->name_it;
            $updatedData['featured'] = isset($request->featured) ? 1 : 0;
            $updatedData['priority'] = $request->priority;
            
            RecipeDiet::where('recipe_diet_id',$id)->update($updatedData);

            $message = "Diet updated successfuly";
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
        $recipeDiet = RecipeDiet::where('recipe_diet_id',$id)->delete();
        if($recipeDiet){
            //$recipeIngredient->delete();
            $message = "Recipe diet deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted it.";
            return redirect()->back()->with('error', $message);
        }
    }
}
