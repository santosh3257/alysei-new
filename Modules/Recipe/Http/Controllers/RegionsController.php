<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeRegion; 
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\Cousin;
use Validator;

class RegionsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $regions = RecipeRegion::with('attachment')->paginate('10');
        return view('recipe::regions.index',compact('regions'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $cousines = Cousin::where('status',1)->get();
        return view('recipe::regions.create',compact('cousines'));
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

            $newRegion = new RecipeRegion;
            $newRegion->image_id = $this->uploadFrontImage($request->file('image'));
            $newRegion->name_en = $request->name_en;
            $newRegion->name_it = $request->name_it;
            $newRegion->featured = isset($request->featured) ? 1 : 0;
            $newRegion->priority = $request->priority;
            $newRegion->cousin_id = $request->cousin_id;
            $newRegion->save();

            $message = "Region added successfuly";
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
        return view('recipe::show',compact('cousines'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $region = RecipeRegion::where('recipe_region_id',$id)->with("attachment")->first();
        $cousines = Cousin::where('status',1)->get();
        return view('recipe::regions.edit',compact('region','id','cousines'));
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
            $updatedData['cousin_id'] = $request->cousin_id;
            
            RecipeRegion::where('recipe_region_id',$id)->update($updatedData);

            $message = "Region updated successfuly";
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
        $recipeRegion = RecipeRegion::where('recipe_region_id',$id)->delete();
        if($recipeRegion){
            //$recipeIngredient->delete();
            $message = "Recipe region deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted it.";
            return redirect()->back()->with('error', $message);
        }
    }
}
