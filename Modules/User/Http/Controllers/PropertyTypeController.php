<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Role; 
use Modules\User\Entities\User; 
use Modules\User\Entities\UserFieldOption; 
use Modules\User\Entities\UserField;
use Modules\User\Entities\UserFieldValue;
use DB;
class PropertyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $userField = UserField::where('user_field_id',2)->get()->toArray();
        foreach($userField as $fieldKey => $fieldValue){

            $userFieldParents = $this->getUserFieldOptionParent($fieldValue['user_field_id']);
            // $userField[$fieldKey]['options'] = $userFieldParents;
            // foreach($userFieldParents as $parentKey => $parentValue){
            //     $data = $this->getUserFieldOptionsNoneParent($fieldValue['user_field_id'],$parentValue->user_field_option_id);
            //     $userField[$fieldKey]['options'][$parentKey]->options = $data;

            //     foreach($data as $key => $value){
            //         $noneParentData = $this->getUserFieldOptionsNoneParent($fieldValue['user_field_id'],$value->user_field_option_id);
            //         $userField[$fieldKey]['options'][$parentKey]->options[$key]->options = $noneParentData; 
            //     }
            // }
        }

        // $userFields = $userField[0]['options'];

        // dd($userFieldParents);
        // die();

        return view('user::property_types.list', compact('userFieldParents'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

        return view('user::property_types.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        // dd($input);
        // die();
        $mainProperty = $input['main_property'];

        $head1 = $input['head1'];
        $head2 = $input['head2'];

        $head1_options = $input['head1_option'];
        $head2_options = $input['head2_option'];

        $propertyType = [];
        $propertyType['user_field_id'] = 2;
        $propertyType['option'] = $mainProperty;
        $parentProperty = UserFieldOption::create($propertyType);

        if($parentProperty){
            
            //save head 1
            $head1Arr = [];
            $head1Arr['user_field_id'] = 2;
            $head1Arr['option'] = $head1;
            $head1Arr['parent'] = $parentProperty->id;
            $head1Arr['head'] = 1;
            $head1Response = UserFieldOption::create($head1Arr);

            foreach($head1_options as $value){
                $child = [];
                $child['user_field_id'] = 2;
                $child['option'] = $value;
                $head1Arr['optionType'] = 'conservation';
                $child['parent'] = $head1Response->id;

                UserFieldOption::create($child);                
            }

            //save head 2
            $head2Arr = [];
            $head2Arr['user_field_id'] = 2;
            $head2Arr['option'] = $head2;
            $head2Arr['parent'] = $parentProperty->id;
            $head2Arr['head'] = 1;
            $head2Response = UserFieldOption::create($head2Arr);

            foreach($head2_options as $value){
                $child = [];
                $child['user_field_id'] = 2;
                $child['option'] = $value;
                $child['parent'] = $head2Response->id;

                UserFieldOption::create($child);                
            }

        }

        $message = "Property Types created successfuly";
        return redirect()->back()->with('success', $message); 

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
    public function edit($fieldId, $optionId, $option)
    {
        $option = base64_decode($option);   
        $options = [];
        $data = $this->getUserFieldOptionsNoneParent($fieldId,$optionId);
        $options = $data;
        foreach($data as $key => $value){
            $noneParentData = $this->getUserFieldOptionsNoneParent($fieldId,$value->user_field_option_id);
            //$count = 0;
            foreach($noneParentData as $idx=>$opt){
                $count = DB::table('user_field_values')->join('users', function($join)
                        {
                            $join->on('user_field_values.user_id', '=', 'users.user_id');
                        })
                        ->where('user_field_values.user_field_id',2)
                        ->where('user_field_values.value','=',$opt->user_field_option_id)
                        ->where('users.deleted_at',null)
                        ->count();
                //$count = UserFieldValue::where('user_field_id',2)->where('value',$opt->user_field_option_id)->count();
                $noneParentData[$idx]->count = $count;
            }
            $options[$key]->options = $noneParentData; 
        }
        // dd($options);
        // die();
        return view('user::property_types.edit', compact('options','option'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request)
    {
        $input = $request->all();
        unset($input['_token']);

        if(array_key_exists('head_0',$input) && count($input['head_0']) > 0){
            $parentId = $input['head0_id'];
            foreach($input['head_0'] as $value){
                $child = [];
                $child['user_field_id'] = 2;
                $child['option'] = $value;
                $child['parent'] = $parentId;
                $child['optionType'] = 'conservation';
                $child['head'] = 0;
                UserFieldOption::create($child);   
            }

            unset($input['head_0']);
            unset($input['head0_id']);
        }

        if(array_key_exists('head_1',$input) && count($input['head_1']) > 0){
            $parentId = $input['head1_id'];
            foreach($input['head_1'] as $value){
                $child = [];
                $child['user_field_id'] = 2;
                $child['option'] = $value;
                $child['parent'] = $parentId;
                $child['head'] = 0;
                UserFieldOption::create($child);   
            }
            unset($input['head_1']);
            unset($input['head1_id']);
        }


        foreach($input as $key => $option){
            if($option){
                UserFieldOption::where('user_field_option_id',$key)->update(['option' => $option]);
            }
        }
        $message = "Property Types updated successfuly";
        return redirect()->back()->with('success', $message); 

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($optionId)
    {
        if($optionId){

            //$valueExists = UserFieldValue::where('user_field_id',2)->where('value',$optionId)->get();
            $valueExists = DB::table('user_field_values')->join('users', function($join)
                            {
                                $join->on('user_field_values.user_id', '=', 'users.user_id');
                            })
                            ->where('user_field_values.user_field_id',2)
                            ->where('user_field_values.value','=',$optionId)
                            ->where('users.deleted_at',null)
                            ->get();

            if(count($valueExists) == 0){
                $delete = UserFieldOption::where('user_field_option_id',$optionId)->delete();

                if($delete){
                    return response()->json(['success' => true,"message" => "Property Type has been deleted"], 200);
                }else{
                    return response()->json(['success' => false,"message" => "something went wrong"], 200);
                }
            }else{
                $selectedUserArray = $valueExists->pluck('user_id')->toArray();
                $users = User::select('user_id','restaurant_name','first_name','last_name','company_name','name','role_id')->whereIn('user_id',$selectedUserArray)->get();
                $userName = '<ul>';
                foreach($users as $key=>$user){
                    if($user->role_id == 9){
                        $userName .= '<li><a target="_blank" href="'.url("dashboard/users/edit", [$user->user_id]).'" title="Edit">'.$user->restaurant_name.'</a></li>';
                    }
                    elseif(!empty($user->first_name))
                    {
                        $userName .= '<li><a target="_blank" href="'.url("dashboard/users/edit", [$user->user_id]).'" title="Edit">'.$user->first_name.' '.$user->last_name.'</a></li>';
                    }
                    elseif(!empty($user->company_name))
                    {
                        $userName .= '<li><a target="_blank" href="'.url("dashboard/users/edit", [$user->user_id]).'" title="Edit">'.$user->company_name.'</a></li>';
                    }
                    else
                    {
                        $userName .= '<li><a target="_blank" href="'.url("dashboard/users/edit", [$user->user_id]).'" title="Edit">'.$user->name.'</a></li>';
                    }
                }

                $userName .= '</ul>';
                return response()->json(['success' => false,"message" => "This product type has already been selected by members ".$userName], 200);
            }

        }
    }

    public function destroyConfirm($optionId)
    {
        if($optionId){

            UserFieldValue::where('user_field_id',2)->where('value',$optionId)->delete();
            UserFieldOption::where('user_field_option_id',$optionId)->delete();
            $message = "Property Type has been deleted";
            return redirect()->back()->with('success', $message);
            //return response()->json(['success' => true,"message" => "Property Type has been deleted"], 200);
        }
    }

    public function getUserFieldOptionParent($fieldId){

        $fieldOptionData = [];
        
        if($fieldId > 0){
            $fieldOptionData = DB::table('user_field_options')
                    ->where('user_field_id','=',$fieldId)
                    ->where('parent','=',0)
                    ->where('deleted_at',null)
                    ->orderBy('option','ASC')
                    ->get()->toArray();

            foreach ($fieldOptionData as $key => $option) {
                $count = 0;
                $propertyFields = UserFieldOption::where('parent',$option->user_field_option_id)->get();
                if($propertyFields){
                    foreach($propertyFields as $field){
                        $propertyOptions = UserFieldOption::where('parent',$field->user_field_option_id)->get();
                        if($propertyOptions){
                            foreach($propertyOptions as $opt){
                                $count += DB::table('user_field_values')->join('users', function($join)
                                            {
                                                $join->on('user_field_values.user_id', '=', 'users.user_id');
                                            })
                                            ->where('user_field_values.user_field_id',2)
                                            ->where('user_field_values.value','=',$opt->user_field_option_id)
                                            ->where('users.deleted_at',null)
                                            ->count();
            
                                //$count += UserFieldValue::where('user_field_id',2)->where('value',$opt->user_field_option_id)->count();
                            }
                        }
                    }
                }
                $fieldOptionData[$key]->count = $count;
                $fieldOptionData[$key]->hint = $option->hint;
                $fieldOptionData[$key]->option = $option->option;
            }

            //if($fieldId == 2){
                array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            //}
        }
        
        return $fieldOptionData;    
        
    }

    /*
     * Get All Fields Option who are child
     * @params $user_field_id and $user_field_option_id
     */
    public function getUserFieldOptionsNoneParent($fieldId, $parentId){

        $fieldOptionData = [];
        
        if($fieldId > 0 && $parentId > 0){
            $fieldOptionData = DB::table('user_field_options')
                ->where('user_field_id','=',$fieldId)
                ->where('deleted_at',null)
                ->where('parent','=',$parentId)
                ->get()->toArray();                                

            foreach ($fieldOptionData as $key => $option) {
                $fieldOptionData[$key]->hint = $option->hint;
                $fieldOptionData[$key]->option = $option->option;
            }

            //if($fieldId == 2){
                array_multisort(array_column( $fieldOptionData, 'option' ), SORT_ASC, $fieldOptionData);
            //}
        }
        
        return $fieldOptionData;    
        
    }

    public function updateOption(Request $request){
        $input = $request->all();
        if(array_key_exists('optionId', $input) && array_key_exists('option', $input)){
            if($input['optionId'] && $input['option']){
                UserFieldOption::where('user_field_option_id',$input['optionId'])->update(['option' => $input['option']]);
                return response()->json(['success' => true,"message" => "Property Type has been updated"], 200);
            }
        }
    }

    // public function tempInsertProperty(){
    //     $data = [];
    //     $data[] = 'Biodynamic';
    //     $data[] = 'Kosher';
    //     $data[] = 'Halal';
    //     $data[] = 'PGI';
    //     $data[] = 'PDO';
    //     $data[] = 'n/a';

    //     $userField = UserField::where('user_field_id',2)->get()->toArray();
    //     foreach($userField as $fieldKey => $fieldValue){

    //         $userFieldParents = $this->getUserFieldOptionParent($fieldValue['user_field_id']);
            
    //          foreach($userFieldParents as $parentKey => $parentValue){
    //             $childs = $this->getUserFieldOptionsNoneParent($fieldValue['user_field_id'],$parentValue->user_field_option_id);
    //             unset($childs[0]);
    //             foreach($data as $value){
    //                 $child = [];
    //                 $child['user_field_id'] = 2;
    //                 $child['option'] = $value;
    //                 $child['parent'] = $childs[1]->user_field_option_id;
    //                 $child['head'] = 0;
    //                 UserFieldOption::create($child);   
    //             }
                   
    //         }
    //     }

    //     die("success");
    // }
}
