<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Country;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Notification;
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use App\Http\Traits\UploadImageTrait;
use DB;
use Illuminate\Support\Str;

class CountryController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $keyword = isset($_GET['keyword'])? $_GET['keyword'] : '';
        $query = Country::with('flagImg');
        if((isset($_GET['keyword'])) && (!empty($_GET['keyword']))){
            $query->Where(function ($q) use ($keyword) {
            $q->where('name', 'LIKE', '%' . $keyword . '%');
            });
        }
        $countries = $query->with('flagImg')->paginate(10);
        return view('user::countries.list', compact('countries'));

        /* $countries = Country::with('flagImg')->paginate(10);
        return view('user::countries.list', compact('countries')); */
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
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
        $country = country::where('id',$id)->with("flagImg")->first();
        return view('user::countries.edit',compact('country'));
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
                'country_name' => 'required',
                'country_status' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];
           if ($request->hasFile('image')) {
            $updatedData['flag_id'] = $this->uploadFrontImage($request->file('image'));
            } 

            $updatedData['name'] = $request->country_name;
            $updatedData['status'] = $request->country_status;
            Country::where('id',$id)->update($updatedData);
            
            $message = "Country updated successfuly";
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
        //
    }
}
