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
use Modules\User\Entities\News; 
use Modules\Activity\Entities\DiscoverAlysei;
use Modules\Activity\Entities\DiscoveryNewsView;

class NewsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $news = News::with('attachment')->paginate(10);
        return view('user::news.list',compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('user::news.create');
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
                    'title' => 'required',
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newNews = new News;
            $newNews->image_id = $this->uploadFrontImage($request->file('image'));
            $newNews->title = $request->title;
            $newNews->slug = Str::slug($request->title, '-');
            $newNews->status = $request->status;
            $newNews->description = $request->description;
            $newNews->created_at = now();
            $newNews->updated_at = now();
            $newNews->save();

            $discoveryAlysei = DiscoverAlysei::where('discover_alysei_id',5)->first();
            if($discoveryAlysei){
                $discoveryAlysei->new_update = 1;
                $discoveryAlysei->save();
                DiscoveryNewsView::where('viewType',$discoveryAlysei->name)->delete();
            }
            $message = "News added successfuly";
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
        $news = News::where('news_id',$id)->first();
        return view('user::news.edit',compact('news'));
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
                'title' => 'required',
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

            $updatedData['title'] = $request->title;
            $updatedData['status'] = $request->status;
            $updatedData['description'] = $request->description;
            $updatedData['updated_at'] = now();
            News::where('news_id',$id)->update($updatedData);
            $message = "News updated successfuly";
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
        $news = News::where('news_id', $id)->first();
        if($news){
            $news->delete();
            $message = "News deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
