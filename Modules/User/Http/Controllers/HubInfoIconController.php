<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\HubInfoIcon; 
use Modules\User\Entities\Role;
use Carbon\Carbon;
use Validator;


class HubInfoIconController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $data = HubInfoIcon::with('roledata')->orderBy('created_at','desc')->paginate(10);
        return view('user::hubinfoicon.list', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin'])->get();
        return view('user::hubinfoicon.create', compact('roles'));
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
                    'role_id' => 'required',
                    'message_en' => 'required',
                    'message_it' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newRole = new HubInfoIcon;
            $newRole->role_id = $request->role_id;
            $newRole->message_en = $request->message_en;
            $newRole->message_it = $request->message_it;
            $newRole->created_at = now();
            $newRole->updated_at = now();
            $newRole->save();

            $message = "Hub info icon added successfuly";
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
        $data = HubInfoIcon::where('id',$id)->first();
        $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin'])->get();
        return view('user::hubinfoicon.edit',compact('data','roles'));
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
                    'role_id' => 'required',
                    'message_en' => 'required',
                    'message_it' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];


            $updatedData['role_id'] = $request->role_id;
            $updatedData['message_en'] = $request->message_en;
            $updatedData['message_it'] = $request->message_it;
            $updatedData['updated_at'] = now();
            
            HubInfoIcon::where('id',$id)->update($updatedData);

            $message = "Hub info icon updated successfuly";
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
        $hubInfoIcon = HubInfoIcon::where('id', $id)->first();
        if($hubInfoIcon){
            $hubInfoIcon->delete();
            $message = "Hub info icon deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
