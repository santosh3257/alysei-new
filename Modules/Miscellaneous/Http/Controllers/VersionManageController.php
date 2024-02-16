<?php

namespace Modules\Miscellaneous\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Miscellaneous\Entities\AppVersion;
use Validator;

class VersionManageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $appVersions = AppVersion::paginate(10);
        return view('miscellaneous::app_version.list', compact('appVersions'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('miscellaneous::app_version.create');
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
                    'version' => 'required',
                    'app' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            $activeVersion = AppVersion::where('status','1')->first();
            $appVersion = new AppVersion;
            if($activeVersion){
                if($request->app == 'android'){
                    $appVersion->android = $request->version;
                    $appVersion->ios = $activeVersion->ios;
                }
                if($request->app == 'ios'){
                    $appVersion->android = $activeVersion->android;
                    $appVersion->ios = $request->version;
                }
                if($request->app == 'both'){
                    $appVersion->android = $request->version;
                    $appVersion->ios = $request->version;
                }
            }
            else{
                if($request->app == 'android'){
                    $appVersion->android = $request->version;
                    $appVersion->ios = $request->version;
                }
                if($request->app == 'ios'){
                    $appVersion->android = $request->version;
                    $appVersion->ios = $request->version;
                }
                if($request->app == 'both'){
                    $appVersion->android = $request->version;
                    $appVersion->ios = $request->version;
                }
            }
            $appVersion->status = '1';
            $appVersion->save();

            if($appVersion){
                if($activeVersion){
                    AppVersion::where('id',$activeVersion->id)->update(['status'=> '0']);
                }
            }

            $message = "Version added successfuly";
            return redirect()->back()->with('success', $message); 

        }
        catch(\Exception $e)
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
        return view('miscellaneous::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $version = AppVersion::where('id',$id)->first();
        return view('miscellaneous::app_version.edit', compact('version'));
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
                    'android' => 'required',
                    'ios' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }

            
            $updatedData['android'] = $request->android;
            $updatedData['ios'] = $request->ios;
            
            AppVersion::where('id',$id)->update($updatedData);

            $message = "Version updated successfuly";
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
