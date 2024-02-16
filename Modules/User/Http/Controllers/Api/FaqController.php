<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\Faq;
use Validator;
class FaqController extends Controller
{

    public $successStatus = 200;
    public $validationStatus = 422;
    public $exceptionStatus = 409;

    public $user = '';

    public function __construct(){

        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();
            return $next($request);
        });
    }

    /**
     * Get Faq based on role id.
     * @return Response
     */
    public function getRoleFaq(Request $request)
    {
        try{
            $user = $this->user;

            $validator = Validator::make($request->all(), [ 
                'role_id' => 'required', 
            ]);

            if ($validator->fails()) { 
                return response()->json(['errors'=>$validator->errors()->first(),'success' => $this->validationStatus], $this->validationStatus);
            }
            
            $language = $request->header('locale');
            $lang = 'en';
            if(!empty($language)){
                $lang = $language;
            }
            //return $request->role_id;
            $faqs = Faq::select('faq_id','role_id','question_in_'.$lang.' as question','answer_in_'.$lang.' as answer')->where('role_id',$request->role_id)->orderBy('created_at','desc')->paginate(10);
            if($faqs){

                return response()->json(['success' => $this->successStatus,
                                        'data' => $faqs,
                                    ], $this->successStatus);
            }
            else{
                $message = "Opps! No record found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);  
            }
        }
        catch(\Exception $e){
            return response()->json(['success'=>false,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }
}
