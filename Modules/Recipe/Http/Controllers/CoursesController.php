<?php

namespace Modules\Recipe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Recipe\Entities\RecipeCourse; 
use Validator;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $courses = RecipeCourse::orderBy('created_at','desc')->paginate('10');
        return view('recipe::courses.list',compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('recipe::courses.create');
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
                    'name_en' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newCourse = new RecipeCourse;
            $newCourse->name_en = $request->name_en;
            $newCourse->name_it = $request->name_it;
            $newCourse->featured = isset($request->featured) ? 1 : 0;
            $newCourse->priority = $request->priority;
            $newCourse->save();

            $message = "Course added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            //dd($e->getMessage());
            return redirect()->back()->with('error', "Something went wrong");   
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
        $course = RecipeCourse::where('recipe_course_id',$id)->first();
        return view('recipe::courses.edit',compact('course','id'));
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

            $updatedData['name_en'] = $request->name_en;
            $updatedData['name_it'] = $request->name_it;
            $updatedData['featured'] = isset($request->featured) ? 1 : 0;
            $updatedData['priority'] = $request->priority;
            
            RecipeCourse::where('recipe_course_id',$id)->update($updatedData);

            $message = "Course updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            //dd($e->getMessage());
            return redirect()->back()->with('error', "Something went wrong");   
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
        $RecipeCourse = RecipeCourse::where('recipe_course_id',$id)->delete();
        if($RecipeCourse){
            //$recipeIngredient->delete();
            $message = "Recipe course deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted it.";
            return redirect()->back()->with('error', $message);
        }
    }
}
