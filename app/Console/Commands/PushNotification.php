<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Miscellaneous\Entities\CronJob;
use Modules\Miscellaneous\Entities\CronProcess;
use Modules\Miscellaneous\Entities\CronTracking;
use Modules\User\Entities\User;
use App\Http\Traits\NotificationTrait;
use Modules\User\Entities\DeviceToken; 
use App\Notification;
use DB;

class PushNotification extends Command
{

    use NotificationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pushNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is used for push notification to the role users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try
        {
            // get cron that is not started(Status === 0)
            $cronTracking = CronTracking::count();
            if($cronTracking == 0){

                // one row insert in cron tracking table
                $insertTracking = new CronTracking;
                $insertTracking['in_process'] = '1';
                $insertTracking->save();

                $admin = User::where('role_id', '1')->first();

                // get cron job users
                $cronJob = CronJob::with('attachment')->where(function ($query) {
                    $query->where('cron_status', '=', '1')
                          ->orWhere('cron_status', '=', '0');
                })->first();

                if($cronJob){
                    $cronProcess = CronProcess::where('cron_job_id',$cronJob->cron_job_id)->where('status','0')->limit(100)->get();
                    if($cronProcess){
                        // Update status with 1 in cron job table
                        $updatedCronStatus['cron_status'] = '1';
                        $updatedCronStatus['remarks'] = 'Cron Job has been initiated';
                        cronJob::where('cron_job_id',$cronJob->cron_job_id)->update($updatedCronStatus);

                        foreach($cronProcess as $key=>$process){
                            
                            $saveNotification = new Notification;
                            $saveNotification->from = $admin->user_id;
                            $saveNotification->to = $process->user_id;
                            $saveNotification->notification_type = '11'; // Only show notification
                            $saveNotification->title_it = $cronJob->cron_job_title;
                            $saveNotification->title_en = $cronJob->cron_job_title_it;
                            $saveNotification->body = $cronJob->message_en;
                            $saveNotification->redirect_to = 'Notification';
                            $saveNotification->redirect_to_id = 0;
                            $saveNotification->sender_name = "Admin";
                            $saveNotification->save();

                            $tokens = DeviceToken::where('user_id', $process->user_id)->get();
                            $selectedLocale = $this->pushNotificationUserSelectedLanguage($process->user_id);
                            if($selectedLocale == 'en'){
                                $title = $cronJob->cron_job_title;
                                $body = $cronJob->message_en;
                            }
                            else{
                                $title = $cronJob->cron_job_title_it;
                                $body = $cronJob->message_it;
                            }
                            $attachmentUrl = '';
                            if(!empty($cronJob->attachment_id) && $cronJob->attachment_id !=null){
                                $attachmentUrl = $cronJob->attachment->base_url.$cronJob->attachment->attachment_url;
                            }

                            if(count($tokens) > 0)
                            {

                                $collectedTokenArray = $tokens->pluck('device_token');

                                $androidResponse = $this->sendNotification($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,$saveNotification->notification_type,$admin->user_id, 'Alysei', null, null, null, null, null,null,null);

                                $iosResponse = $this->sendNotificationToIOS($collectedTokenArray, $title, $saveNotification->redirect_to, $saveNotification->redirect_to_id,$saveNotification->notification_type, $admin->user_id,'Alysei', null, null, null, null, null,null,null);

                                $androidRes = json_decode($androidResponse);
                                $iosRes = json_decode($iosResponse);
                                if($androidRes->success == 1 || $iosRes->success == 1 ){
                                    $updatedData['status'] = '2';
                                    CronProcess::where('notification_cron_process_id',$process->notification_cron_process_id)->update($updatedData);
                                }
                                else{
                                    $updatedData['status'] = '3';
                                    CronProcess::where('notification_cron_process_id',$process->notification_cron_process_id)->update($updatedData);
                                }
                            }
                            else{
                                $updatedData['status'] = '3';
                                CronProcess::where('notification_cron_process_id',$process->notification_cron_process_id)->update($updatedData);
                            }

                            
                           
                        }

                        
                    }
                    // Check all notification sent to the user
                    $allSent = CronProcess::where('cron_job_id',$cronJob->cron_job_id)->where('status','0')->count();
                    if($allSent == 0){
                        $updatedCron['cron_status'] = '2';
                        $updatedCron['remarks'] = 'Cron Job has been completed';
                        cronJob::where('cron_job_id',$cronJob->cron_job_id)->update($updatedCron);
                    }
                }
                
                // delete row when notification sent
                CronTracking::where('in_process','1')->delete();
            }
        }catch(\Exception $e)
        {
            dd($e->getMessage());
            
        }
    }
}
