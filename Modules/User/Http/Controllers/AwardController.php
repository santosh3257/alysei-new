<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Medal; 
use App\Http\Traits\UploadImageTrait;
use Validator;

class AwardController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function medalsList()
    {
        $medals = Medal::paginate('10');
        return view('user::medals.index',compact('medals'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function createMedal()
    {
        return view('user::medals.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function saveMedal(Request $request)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'name' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newIngredient = new Medal;
            $newIngredient->name = $request->name;
            $newIngredient->save();

            $message = "Medals added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('user::medals.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function editMedal($id)
    {
        $medal = Medal::where('medal_id',$id)->first();
        return view('user::medals.edit',compact('medal','id'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function updateMedal(Request $request, $id)
    {
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'name' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            
            $updatedData['name'] = $request->name;
            
            Medal::where('medal_id',$id)->update($updatedData);

            $message = "Medal updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function deleteMedal($id)
    {
        
        $medal = Medal::where('medal_id',$id)->delete();
        if($medal){
            $message = "Medal deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "Something went wrong";
            return redirect()->back()->with('error', $message);
        }
    }
}
