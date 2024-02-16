<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\UserField;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Support\Str;

class RegistrationFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $fields = UserField::orderBy('user_field_id','desc')->paginate(10);
        return view('user::registration.fields.list', compact('fields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::registration.fields.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try{

            $validator = Validator::make($request->all(), [ 
                'title_en' => 'required',
                'placeholder_en' => 'required',
                'type' => 'required'
            ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $userField = new UserField;
            $userField->title_en = $request->title_en;
            $userField->title_it = $request->title_it;
            $userField->name = Str::slug($request->title_en, '_');
            $userField->placeholder_en = $request->placeholder_en;
            $userField->placeholder_it = $request->placeholder_it;
            $userField->type = $request->type;
            $userField->hint_en = $request->hint_en;
            $userField->hint_it = $request->hint_it;
            $userField->required = $request->required;
            $userField->conditional = $request->conditional;
            $userField->multiple_option = 'false';
            $userField->api_call = 'false';
            $userField->require_update = $request->require_update;
            $userField->display_on_registration = $request->display_on_registration;
            $userField->display_on_dashboard = $request->display_on_dashboard;
            $userField->created_at = now();
            $userField->updated_at = now();
            $userField->save();

            $message = "Field added successfuly";
            return redirect()->back()->with('success', $message); 

          
        }
        catch(\Exception $e){
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
        $field = UserField::where('user_field_id',$id)->first();
        return view('user::registration.fields.edit',compact('field'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try{

            $validator = Validator::make($request->all(), [ 
                'title_en' => 'required',
                'placeholder_en' => 'required',
                'type' => 'required'
            ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }


            $updatedData['title_en'] = $request->title_en;
            $updatedData['title_it'] = $request->title_it;
            $updatedData['name'] = Str::slug($request->title_en, '_');
            $updatedData['placeholder_en'] = $request->placeholder_en;
            $updatedData['placeholder_it'] = $request->placeholder_it;
            $updatedData['type'] = $request->type;
            $updatedData['hint_en'] = $request->hint_en;
            $updatedData['hint_it'] = $request->hint_it;
            $updatedData['required'] = $request->required;
            $updatedData['conditional'] = $request->conditional;
            $updatedData['require_update'] = $request->require_update;
            $updatedData['display_on_registration'] = $request->display_on_registration;
            $updatedData['display_on_dashboard'] = $request->display_on_dashboard;

            UserField::where('user_field_id',$id)->update($updatedData);
            $message = "Field updated successfuly";
            return redirect()->back()->with('success', $message); 

          
        }
        catch(\Exception $e){
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
        //
    }
}
