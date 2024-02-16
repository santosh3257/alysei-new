<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoreController;
use Modules\User\Entities\User; 
use Modules\User\Entities\Role; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Notification;
use Modules\User\Entities\DeviceToken; 
use App\Http\Traits\NotificationTrait;
use App\Http\Traits\UploadImageTrait;
use DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $roles = Role::with('attachment')->paginate(10);
        return view('user::role.list', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::role.create');
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
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newRole = new Role;
            $newRole->image_id = $this->uploadFrontImage($request->file('image'));
            $newRole->name = $request->name;
            $newRole->slug = Str::slug($request->name, '_');
            $newRole->display_name = $request->display_name;
            $newRole->description_en = $request->description_en;
            $newRole->description_it = $request->description_it;
            $newRole->order = $request->order;
            $newRole->created_at = now();
            $newRole->updated_at = now();
            $newRole->save();

            $message = "Role added successfuly";
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
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $role = Role::where('role_id',$id)->with("attachment")->first();
        return view('user::role.edit',compact('role'));
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

            $updatedData = [];

            if($request->file('image')){
                if(!empty($request->file('image'))){
                    $updatedData['image_id'] = $this->uploadFrontImage($request->file('image'));  
                }  
            }

            $updatedData['name'] = $request->name;
            //$updatedData['slug'] = Str::slug($request->name, '_');
            $updatedData['display_name'] = $request->display_name;
            $updatedData['description_en'] = $request->description_en;
            $updatedData['description_it'] = $request->description_it;
            $updatedData['order'] = $request->order;
            $updatedData['updated_at'] = now();
            
            Role::where('role_id',$id)->update($updatedData);

            $message = "Hub updated successfuly";
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
