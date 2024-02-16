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
use Modules\Activity\Entities\DiscoverAlysei; 
use Modules\User\Entities\DiscoveryPost; 
use Modules\User\Entities\DiscoveryPostCategory; 

class DiscoverAlyseiController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $discoverynews = DiscoverAlysei::with('attachment')->paginate(10);
        return view('user::discoverynews.list',compact('discoverynews'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $postCategories = DiscoveryPostCategory::all();
        return view('user::discoverynews.create', compact('postCategories'));
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

            $newNews = new DiscoverAlysei;
            $newNews->image_id = $this->uploadFrontImage($request->file('image'));
            $newNews->title = $request->title;
            $newNews->title_it = $request->title_it;
            $newNews->name = Str::slug($request->title, '-');
            $newNews->status = $request->status;
            $newNews->category_id = $request->category_id;
            $newNews->created_at = now();
            $newNews->updated_at = now();
            $newNews->save();

            $message = "Circle added successfuly";
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
        $news = DiscoverAlysei::where('discover_alysei_id',$id)->first();
        $postCategories = DiscoveryPostCategory::all();
        return view('user::discoverynews.edit',compact('news','postCategories'));
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
            $updatedData['title_it'] = $request->title_it;
            $updatedData['name'] = Str::slug($request->title, '-');
            $updatedData['status'] = $request->status;
            $updatedData['category_id'] = $request->category_id;
            $updatedData['updated_at'] = now();
            DiscoverAlysei::where('discover_alysei_id',$id)->update($updatedData);
            $message = "Circle updated successfuly";
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
        $discover = DiscoverAlysei::where('discover_alysei_id', $id)->first();
        if($discover){
            $discover->delete();
            $message = "Circle deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }

    // Get Discovery Posts
    public function discoveryPost(){
        $postCategories = DiscoveryPostCategory::all();
        $discoverPosts = DiscoveryPost::with('attachment','category')->paginate(20);
        return view('user::discoverypost.index',compact('postCategories','discoverPosts'));
    }

    public function discoveryCreatePost(){
        $postCategories = DiscoveryPostCategory::all();
        $countries = json_decode(file_get_contents(storage_path() . "/country/countryWithCode.json"), true);
        return view('user::discoverypost.create',compact('countries','postCategories'));
    }

    public function storeDiscoveryPost(Request $request){
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'title' => 'required',
                    'title_it' => 'required',
                    'email' => 'required|email',
                    'country_code' => 'required',
                    'phone' => 'required',
                    'category' => 'required',
                    'status' => 'required|in:0,1',
                    'image' => 'required'
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $post = new DiscoveryPost;
            $post->image_id = $this->uploadFrontImage($request->file('image'));
            $post->title = $request->title;
            $post->title_it = $request->title_it;
            $post->slug = Str::slug($request->title, '-');
            $post->email = $request->email;
            $post->country_code = $request->country_code;
            $post->phone_number = $request->phone;
            $post->category_id = $request->category;
            $post->description = $request->description;
            $post->status = $request->status;
            $post->url = $request->url;
            $post->created_at = now();
            $post->updated_at = now();
            $post->save();
            $message = "post added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }

    public function discoveryEditPost($id){
        $post = DiscoveryPost::find($id);
        $postCategories = DiscoveryPostCategory::all();
        $countries = json_decode(file_get_contents(storage_path() . "/country/countryWithCode.json"), true);
        return view('user::discoverypost.edit',compact('postCategories','post','countries'));
    }

    public function discoveryUpdatePost(Request $request, $id){
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'title' => 'required',
                    'title_it' => 'required',
                    'email' => 'required|email',
                    'country_code' => 'required',
                    'phone' => 'required',
                    'category' => 'required',
                    'status' => 'required|in:0,1',
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
            $updatedData['title_it'] = $request->title_it;
            $updatedData['slug'] = Str::slug($request->title, '-');
            $updatedData['email'] = $request->email;
            $updatedData['country_code'] = $request->country_code;
            $updatedData['phone_number'] = $request->phone;
            $updatedData['category_id'] = $request->category;
            $updatedData['description'] = $request->description;
            $updatedData['status'] = $request->status;
            $updatedData['url'] = $request->url;
            $updatedData['updated_at'] = now();
            DiscoveryPost::where('id',$id)->update($updatedData);
            $message = "post updated successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            dd($e->getMessage());
        }
    }

    public function descoveryPostDestroy($id){
        $discoverPost = DiscoveryPost::where('id', $id)->first();
        if($discoverPost){
            $discoverPost->delete();
            $message = "Post deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "We can't be deleted";
            return redirect()->back()->with('error', $message);
        }
    }
}
