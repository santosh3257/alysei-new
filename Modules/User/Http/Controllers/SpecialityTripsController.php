<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\UserFieldOption;
use Validator;

class SpecialityTripsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $specialityTrips = UserFieldOption::where('user_field_id',14)->paginate(10);
        return view('user::speciality_trips.list', compact('specialityTrips'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::speciality_trips.create');
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

            $specility_trips = new UserFieldOption;
            $specility_trips->option = $request->name;
            $specility_trips->user_field_id = 14;
            $specility_trips->save();

            $message = "Speciality trips added successfuly";
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
        $specilityTrips = UserFieldOption::where('user_field_option_id',$id)->first();
        return view('user::speciality_trips.edit',compact('specilityTrips'));
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

            $message = "Speciality trips updated successfuly";
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
        $specialityTrips = UserFieldOption::where('user_field_option_id',$id)->delete();
        if($specialityTrips){
            $message = "Speciality trip deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "Something went wrong";
            return redirect()->back()->with('error', $message);
        }
    }
}
