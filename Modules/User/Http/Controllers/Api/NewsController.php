<?php

namespace Modules\User\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth; 
use Modules\User\Entities\News;

class NewsController extends Controller
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
     * Display a listing of the resource.
     * @return Response
     */
    public function getAllNews(Request $request)
    {
        try
        {
            $news = News::select('news_id','title','image_id')->where('status', 'publish')->with('attachment')->orderBy('created_at','desc')->get();
            if($news){
                
                return response()->json(['success' => $this->successStatus,'data' => $news,], $this->successStatus);
            }
            else{
                $message = "Opps! No record found";
                return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => $this->translate('messages.'.$message,$message)]], $this->exceptionStatus);  
            }
        }
        catch(\Exception $e)
        {
            return response()->json(['success'=>$this->exceptionStatus,'errors' =>['exception' => [$e->getMessage()]]], $this->exceptionStatus); 
        }
    }

    
}
