<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use App\Http\Traits\UploadImageTrait;
use Modules\Marketplace\Entities\MarketplaceBanner; 

class TopBannerController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $query = MarketplaceBanner::with('attachment');
        if((isset($_GET['filter'])) && (!empty($_GET['filter'])) && ($_GET['filter'] != 'all')){
            
            $query->where('type',$_GET['filter']);
        }
        $banners = $query->orderBy('marketplace_banner_id', 'DESC')->paginate(10);
        return view('marketplace::banners.list',compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('marketplace::banners.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        /* echo '<pre>';
        print_r($request->all());
        echo '</pre>';
        echo $request->file('image');
        die();  */
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'title' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $newBanner = new MarketplaceBanner;
            //$newBanner->image_id = $this->uploadImage($request->file('image'));
            if(!empty($request->crop_image)){
            $newBanner->image_id = $this->cropUploadImage($request->crop_image);
            }

            $newBanner->title = $request->title;
            $newBanner->type = $request->type;
            $newBanner->status = $request->status;
            $newBanner->updated_at = now();
            $newBanner->save();

            $message = "Banner added successfuly";
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
        return view('marketplace::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $banner = MarketplaceBanner::where('marketplace_banner_id',$id)->first();
        return view('marketplace::banners.edit',compact('banner'));
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

            if($request->crop_image){
                if(!empty($request->crop_image)){
                    $updatedData['image_id'] = $this->cropUploadImage($request->crop_image);  
                }  
            }

            $updatedData['title'] = $request->title;
            //$updatedData['slug'] = Str::slug($request->name, '_');
            $updatedData['type'] = $request->type;
            $updatedData['status'] = $request->status;
            $updatedData['updated_at'] = now();
            
            MarketplaceBanner::where('marketplace_banner_id',$id)->update($updatedData);

            $message = "Banner updated successfuly";
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
        $banner = MarketplaceBanner::where('marketplace_banner_id', $id)->first();
        if($banner){
            $banner->delete();
            $message = "Banner deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
