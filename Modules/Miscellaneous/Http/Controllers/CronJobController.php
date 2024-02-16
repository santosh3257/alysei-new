<?php

namespace Modules\Miscellaneous\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Miscellaneous\Entities\CronJob;
use Modules\Miscellaneous\Entities\CronProcess;
use Modules\Miscellaneous\Entities\CronTracking;
use Modules\User\Entities\User;
use App\Http\Traits\UploadImageTrait;
use Modules\User\Entities\Role;
use Validator;
use DB;

class CronJobController extends Controller
{
    use UploadImageTrait;
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $cronsData = CronJob::paginate(10);
        return view('miscellaneous::push_notification.list', compact('cronsData'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $roles =  Role::select('role_id','name')->whereNotIn('slug',['super_admin','admin'])->get();
        $users = User::select('user_id','name','first_name','last_name','email')->where('profile_percentage',100)->whereNotIn('role_id',[1,2])->get();
        return view('miscellaneous::push_notification.create', compact('roles','users'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {   
            $validator = Validator::make($request->all(), [ 
                    'message_en' => 'required',
                    'message_it' => 'required',
                    'cron_job_title' => 'required',
                    'cron_job_title_it' => 'required',
                ]);

            if ($validator->fails()) { 

                return redirect()->back()->with('error', $validator->errors()->first());   
            }


            $notification = new CronJob;
            if(!empty($request->file('image'))){
                $notification->attachment_id = $this->uploadImage($request->file('image'));
            }
            $notification->cron_job_title = $request->cron_job_title;
            $notification->cron_job_title_it = $request->cron_job_title_it;
            $notification->message_en = $request->message_en;
            $notification->message_it = $request->message_it;
            $notification->created_at = now();
            $notification->updated_at = now();
            $notification->save();
            if($request->notificationType == 'role'){
                $roles = $request->roles;
                if(!empty($roles)){
                    foreach($request->roles as $key=>$roleId){
                        $users = User::where('role_id',$roleId)->get();
                        if($users){
                            foreach($users as $key=>$user){
                                $cronProcess = new CronProcess;
                                $cronProcess->cron_job_id = $notification->id;
                                $cronProcess->user_id = $user->user_id;
                                $cronProcess->save();
                            }
                        }
                    }
                }
            }
            else{

                if(!empty($request->users)){
                    foreach($request->users as $key=>$user){
                            $cronProcess = new CronProcess;
                            $cronProcess->cron_job_id = $notification->id;
                            $cronProcess->user_id = $user;
                            $cronProcess->save();
                    }
                }
            }
            DB::commit();
            $message = "Notification added successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e)
        {
            DB::rollback();
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
        return view('miscellaneous::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
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

    /**
     * Send Notification
     * @param int $userId
     * @return Response
     **/
    public function getCronProcessData($id){
        $users = CronProcess::with('user:user_id,name,email,first_name,last_name,company_name,restaurant_name')->where('cron_job_id',$id)->paginate(50);
        return $users;
        return view('_itemlist', compact('items'));
       
    }


}
