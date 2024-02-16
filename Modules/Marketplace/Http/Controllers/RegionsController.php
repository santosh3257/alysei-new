<?php

namespace Modules\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\User\Entities\State;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\Cousin;
use Validator;

class RegionsController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $regions = State::select('id','name','flag_id')->with('attachment')->where('status', '1')->where('country_id', 107)->orderBy('name', 'ASC')->paginate(10);
        return view('marketplace::regions.index',compact('regions'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $cousines = Cousin::where('status',1)->get();
        return view('marketplace::regions.create',compact('cousines'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        // try
        // {   
        //     $validator = Validator::make($request->all(), [ 
        //             'name_en' => 'required',
        //             'image' => 'required'
        //         ]);

        //     if ($validator->fails()) { 

        //         return redirect()->back()->with('error', $validator->errors()->first());   
        //     }

        //     $newRegion = new MarketplaceRegion;
        //     $newRegion->image_id = $this->uploadImage($request->file('image'));
        //     $newRegion->name_en = $request->name_en;
        //     $newRegion->name_it = $request->name_it;
        //     $newRegion->featured = isset($request->featured) ? 1 : 0;
        //     $newRegion->priority = $request->priority;
        //     $newRegion->cousin_id = $request->cousin_id;
        //     $newRegion->save();

        //     $message = "Region added successfuly";
        //     return redirect()->back()->with('success', $message); 

        // }catch(\Exception $e)
        // {
        //     dd($e->getMessage());
            
        // }
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
        $region = State::where('id',$id)->with("attachment")->first();
        $cousines = Cousin::where('status',1)->get();
        return view('marketplace::regions.edit',compact('region','id','cousines'));
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
                $updatedData['flag_id'] = $this->uploadImage($request->file('image'));    
            }

            $updatedData['name'] = $request->name;
            // $updatedData['name_it'] = $request->name_it;
            // $updatedData['featured'] = isset($request->featured) ? 1 : 0;
            // $updatedData['priority'] = $request->priority;
            // $updatedData['cousin_id'] = $request->cousin_id;
            
            State::where('id',$id)->update($updatedData);

            $message = "Region updated successfuly";
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
        //
    }
}
