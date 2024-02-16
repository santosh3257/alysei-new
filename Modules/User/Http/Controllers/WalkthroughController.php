<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Http\Traits\UploadImageTrait;
use Illuminate\Support\Str;
use Modules\User\Entities\Walkthrough; 
use Modules\User\Entities\Role;

class WalkthroughController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $request->type;
        $roles = Role::select('role_id','name')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->orderBy('order')->get();

        $query = Walkthrough::with('attachment')->where('type','alysei');
        if((isset($_GET['filter'])) && (!empty($_GET['filter']))){
            if($_GET['filter'] == 'alysei'){
                $query->where('role_id',0);
            }
            else{
                $query->where('role_id',$_GET['filter']);
            }
        }
        $walkthroughs = $query->orderBy('walk_through_screen_id', 'DESC')->paginate(10);
        return view('user::walkthrough.index',compact('walkthroughs','roles'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $roles = Role::select('role_id','name')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->orderBy('order')->get();
        return view('user::walkthrough.create',compact('roles'));
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
                    'description_en' => 'required',
                    'description_it' => "required",
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $walkthrough = new Walkthrough;
            $walkthrough->role_id = $request->role_id;
            $walkthrough->image_id = $this->uploadWalkthroughImage($request->file('image'));
            $walkthrough->title_en = $request->title_en;
            $walkthrough->title_it = $request->title_it;
            $walkthrough->description_en = $request->description_en;
            $walkthrough->description_it = $request->description_it;
            $walkthrough->step = 'step'.$request->order;
            $walkthrough->order = $request->order;
            $walkthrough->created_at = now();
            $walkthrough->updated_at = now();
            $walkthrough->save();

            $message = "Walkthrough added successfuly";
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
        $walkthrough = Walkthrough::where('walk_through_screen_id',$id)->first();
        $roles = Role::select('role_id','name')->whereNotIn('slug',['super_admin','admin','importer','distributer'])->orderBy('order')->get();
        return view('user::walkthrough.edit',compact('walkthrough','roles'));
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
                    'description_en' => 'required',
                    'description_it' => "required"
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];

            if($request->file('image')){
                if(!empty($request->file('image'))){
                    $updatedData['image_id'] = $this->uploadWalkthroughImage($request->file('image'));  
                }  
            }

            $updatedData['role_id'] = $request->role_id;
            $updatedData['title_en'] = $request->title_en;
            $updatedData['title_it'] = $request->title_it;
            $updatedData['description_en'] = $request->description_en;
            $updatedData['description_it'] = $request->description_it;
            $updatedData['step'] = 'step'.$request->order;
            $updatedData['order'] = $request->order;
            $updatedData['updated_at'] = now();
            Walkthrough::where('walk_through_screen_id',$id)->update($updatedData);
            $message = "Walkthrough updated successfuly";
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
        $walkthrough = Walkthrough::where('walk_through_screen_id', $id)->first();
        if($walkthrough){
            $walkthrough->delete();
            $message = "Walkthrough deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
