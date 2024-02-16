<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeTool; 
use App\Http\Traits\UploadImageTrait;
use Validator;

class ToolsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $tools = RecipeTool::with('attachment')->paginate('10');
        return view('recipe::tools.index',compact('tools'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $tools = RecipeTool::select("title_en","recipe_tool_id")->where('parent',0)->get();
        return view('recipe::tools.create',compact('tools'));
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
                    'title_en' => 'required',
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newTool = new RecipeTool;
            $newTool->image_id = $this->uploadFrontImage($request->file('image'));
            $newTool->title_en = $request->title_en;
            $newTool->title_it = $request->title_it;
            $newTool->name = strtolower(str_replace(' ', '_', $request->title));
            $newTool->featured = isset($request->featured) ? 1 : 0;
            $newTool->priority = $request->priority;
            $newTool->parent = isset($request->parent) ? 0 : $request->parent_id;
            $newTool->save();

            $message = "Tool added successfuly";
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
        $tool = RecipeTool::where('recipe_tool_id',$id)->with("attachment")->first();
        $parentTools = RecipeTool::select("title_en","recipe_tool_id")->where('parent',0)->get();
        $displayNone = "style=display:none";
        return view('recipe::tools.edit',compact('tool','parentTools','displayNone','id'));
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
                    'title_en' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];

            if($request->file('image')){
                $updatedData['image_id'] = $this->uploadFrontImage($request->file('image'));    
            }

            $updatedData['title_en'] = $request->title_en;
            $updatedData['title_it'] = $request->title_it;
            $updatedData['name'] = strtolower(str_replace(' ', '_', $request->title_en));
            $updatedData['featured'] = isset($request->featured) ? 1 : 0;
            $updatedData['priority'] = $request->priority;
            $updatedData['parent'] = isset($request->parent) ? 0 : $request->parent_id;

            RecipeTool::where('recipe_tool_id',$id)->update($updatedData);

            $message = "Tool updated successfuly";
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
        $recipeTool = RecipeTool::where('recipe_tool_id',$id)->delete();
        if($recipeTool){
            //$recipeIngredient->delete();
            $message = "Recipe tool deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted it.";
            return redirect()->back()->with('error', $message);
        }
    }
}
