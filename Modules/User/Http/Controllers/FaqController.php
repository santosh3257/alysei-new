<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Faq; 
use Modules\User\Entities\Role;
use Carbon\Carbon;
use Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $faqs = Faq::with('roledata')->orderBy('created_at','desc')->paginate(10);
        return view('user::faq.list', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin'])->get();
        return view('user::faq.create', compact('roles'));
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
                    'question_in_en' => 'required',
                    'question_in_it' => 'required',
                    'answer_in_en' => 'required',
                    'answer_in_it' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newRole = new Faq;
            $newRole->role_id = $request->role_id;
            $newRole->question_in_en = $request->question_in_en;
            $newRole->question_in_it = $request->question_in_it;
            $newRole->answer_in_en = $request->answer_in_en;
            $newRole->answer_in_it = $request->answer_in_it;
            $newRole->created_at = now();
            $newRole->updated_at = now();
            $newRole->save();

            $message = "Faq added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
        $profileMode = User::select('who_can_view_profile')->where('user_id',$roleUser)->first();
        if($profileMode->who_can_view_profile == 'anyone'){
            array_push($hubSelectedUsers, $roleUser);
        }
        elseif($profileMode->who_can_view_profile == 'followers'){
            $myFollowers = Follower::select('*','follow_user_id as poster_id')->where('user_id', $user->user_id)->pluck('poster_id');
            if($myFollowers){
                array_push($hubSelectedUsers, $roleUser);
            }
        }
        elseif($profileMode->who_can_view_profile == 'connections'){
            // Get user connections
            $requestedConnection = Connection::select('*','user_id as poster_id')->where('resource_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');
            $getRequestedConnection = Connection::select('*','resource_id as poster_id')->where('user_id', $user->user_id)->where('is_approved', '1')->pluck('poster_id');

            $merged = $requestedConnection->merge($getRequestedConnection);
            $myConnections = $merged->all();


            if(!empty($myConnections)){
                array_push($hubSelectedUsers, $roleUser);
            }
        }
        elseif($profileMode->who_can_view_profile == 'justme'){
             if (in_array($user->user_id, $users)){
                    array_push($hubSelectedUsers, $user->user_id);
                }
        }
        else{
            if (in_array($user->user_id, $users)){
                    array_push($hubSelectedUsers, $user->user_id);
                }
        }    }

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
        $faq = Faq::where('faq_id',$id)->first();
        $roles = Role::select('role_id','name','slug')->whereNotIn('slug',['super_admin','admin'])->get();
        return view('user::faq.edit',compact('faq','roles'));
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
                    'question_in_en' => 'required',
                    'question_in_it' => 'required',
                    'answer_in_en' => 'required',
                    'answer_in_it' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $updatedData = [];


            $updatedData['role_id'] = $request->role_id;
            $updatedData['question_in_en'] = $request->question_in_en;
            $updatedData['question_in_it'] = $request->question_in_it;
            $updatedData['answer_in_en'] = $request->answer_in_en;
            $updatedData['answer_in_it'] = $request->answer_in_it;
            $updatedData['updated_at'] = now();
            
            Faq::where('faq_id',$id)->update($updatedData);

            $message = "Faq updated successfuly";
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

        $faq = Faq::where('faq_id', $id)->first();
        if($faq){
            $faq->delete();
            $message = "Faq deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
