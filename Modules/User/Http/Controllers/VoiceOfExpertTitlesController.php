<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\UserFieldOption;
use Validator;

class VoiceOfExpertTitlesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $exprtTitles = UserFieldOption::where('user_field_id',12)->paginate(10);
        return view('user::expert_titles.list', compact('exprtTitles'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::expert_titles.create');
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
                    'name' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $restaurantType = new UserFieldOption;
            $restaurantType->option = $request->name;
            $restaurantType->user_field_id = 12;
            $restaurantType->save();

            $message = "Expert title added successfuly";
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
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $expertTitle = UserFieldOption::where('user_field_option_id',$id)->first();
        return view('user::expert_titles.edit',compact('expertTitle'));
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
                    'name' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            
            $updatedData['option'] = $request->name;
            
            UserFieldOption::where('user_field_option_id',$id)->update($updatedData);

            $message = "Expert title updated successfuly";
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
    public function destroy($id)
    {
        $expertTitle = UserFieldOption::where('user_field_option_id',$id)->delete();
        if($expertTitle){
            $message = "Expert title deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "Something went wrong";
            return redirect()->back()->with('error', $message);
        }
    }
}
