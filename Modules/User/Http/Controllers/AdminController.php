<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use App\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
use Modules\User\Entities\Role;
use Modules\Activity\Entities\ActivityAction;
use DB;
use Modules\Marketplace\Entities\MarketplaceProduct; 
use Modules\Marketplace\Entities\MarketplaceStore;
//use App\Events\UserRegisterEvent;
use Modules\User\Entities\SiteLanguage;
class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
   

    /***
    dashboard
    ***/
    public function dashboard(Request $request)
    {
        $roles = Role::Leftjoin('users', 'roles.role_id', '=', 'users.role_id')->select('roles.name','roles.role_id', DB::raw('COUNT(users.role_id ) as totalUsers'))->whereNull('users.deleted_at')->groupBy('users.role_id')->whereNotIn("roles.role_id",[1,2])->orderBy("roles.name",'asc')->get();

        $posts  = ActivityAction::orderBy("created_at",'desc')->count();
        $products  = MarketplaceProduct::count();
        $stores  = MarketplaceStore::count();
        
        $currentYear = date('Y');
        $usersStats = DB::table('users')
              ->select(DB::raw('count(user_id) as `data`'), DB::raw("DATE_FORMAT(users.created_at, '%m-%Y') new_date"),  DB::raw('YEAR(users.created_at) year, MONTH(users.created_at) month'),'users.role_id','roles.name','roles.slug')
            ->join('roles','roles.role_id','=','users.role_id')
            ->where('users.role_id','!=',1)
            ->where('deleted_at',null)
            ->whereYear('users.created_at',$currentYear)
            ->groupby('year','month','users.role_id')
            ->get();
        
        $newArr = [];
        $count = 0;
        $months = [1,2,3,4,5,6,7,8,9,10,11,12];
        foreach($usersStats as $key => $stat){
                if($stat->slug === 'importer'){
                    $stat->name = 'Importers';
                }

                if($stat->slug === 'Importer_and_Distributer'){
                    $stat->name = 'Importers and Distributors';
                }

                if($stat->slug === 'distributer'){
                    $stat->name = 'Distributers';
                }

                $newArr[$stat->name][$stat->month] = $stat->data;
        }

        foreach($newArr as $key => $stat){
            $data = [];
            foreach($months as $monthKey => $month){
                if(array_key_exists($month,$stat)){
                    $data[] = $stat[$month];
                }else{
                    $data[] = 0;
                }

            }

            $newArr[$key] = ["label" => $key,
                             "data" => $data,
                             "fill" => false,
                             "tension" => "0.1"];
        }
        
        $finalArr = [];
        foreach($newArr as $value){
            $finalArr[] = $value;
        }

        
        $userStats = json_encode($finalArr);

        $activityPostStats = DB::table('activity_actions')
              ->select(DB::raw('count(activity_action_id) as `data`'), DB::raw("DATE_FORMAT(activity_actions.created_at, '%m-%Y') new_date"),  DB::raw('YEAR(activity_actions.created_at) year, MONTH(activity_actions.created_at) month'))
            ->where('deleted_at',null)
            ->whereYear('activity_actions.created_at',$currentYear)
            ->groupby('year','month')
            ->get();

        $actualData = [];
        foreach($months as $month){
            $data = 0;
            foreach($activityPostStats as $key =>$value){
                if($month === $value->month){
                    $data = $value->data;
                }
            }
            $actualData[] = $data;
        }
        
        $activityPostStatsData = ["label" => "Posts",
                             "data" => $actualData,
                             "fill" => false,
                             "tension" => "0.1",
                             "borderColor" => 'rgb(75, 192, 192)'];
        
        $postStats = json_encode($activityPostStatsData);

        $MarketplaceStoreStats = DB::table('marketplace_stores')
              ->select(DB::raw('count(marketplace_store_id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
            ->where('deleted_at',null)
            ->whereYear('created_at',$currentYear)
            ->groupby('year','month')
            ->get();

        $actualData = [];
        foreach($months as $month){
            $data = 0;
            foreach($MarketplaceStoreStats as $key =>$value){
                if($month === $value->month){
                    $data = $value->data;
                }
            }
            $actualData[] = $data;
        }
        
        $marketplaceStatsData = ["label" => "Stores",
                             "data" => $actualData,
                             "fill" => false,
                             "tension" => "0.1",
                             "borderColor" => 'rgb(75, 192, 192)'];
        
        $storeStats = json_encode($marketplaceStatsData);

        $MarketplaceProductStats = DB::table('marketplace_products')
              ->select(DB::raw('count(marketplace_store_id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
            ->where('deleted_at',null)
            ->whereYear('created_at',$currentYear)
            ->groupby('year','month')
            ->get();

        $actualData = [];
        foreach($months as $month){
            $data = 0;
            foreach($MarketplaceProductStats as $key =>$value){
                if($month === $value->month){
                    $data = $value->data;
                }
            }
            $actualData[] = $data;
        }
        
        $marketplaceProductsData = ["label" => "Products",
                             "data" => $actualData,
                             "fill" => false,
                             "tension" => "0.1",
                             "borderColor" => 'rgb(75, 192, 192)'];
        
        $productStats = json_encode($marketplaceProductsData);

        $hello = trans('messages.email_changed');

        return view('admin.home', compact("roles", "posts","stores","products","userStats","postStats","storeStats","productStats","hello"));
    }

    
    /***
    logout
    ***/
    public function logout(Request $request)
    {
        Auth::logout();
        return Redirect('login');
    }

    // Website Localization 
    public function getSiteLocalization(Request $request){
        
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $query = SiteLanguage::where('id','!=',null);
        if((isset($_GET['keyword'])) && (!empty($_GET['keyword']))){
            $query->Where(function ($q) use ($keyword) {
            $q->orWhere('key', 'LIKE', '%' . $keyword . '%')
                ->orWhere('en', 'LIKE', '%' . $keyword . '%')
                ->orWhere('it', 'LIKE', '%' . $keyword . '%');
            });
        }

        $languages = $query->orderBy('key', 'ASC')->paginate(10);
        //$languages = SiteLanguage::paginate(10);
        
        return view('user::localization.list', compact('languages','keyword'));
    }

    // Website Localization 
    public function createSiteLocalization(Request $request){
        
        return view('user::localization.create');
    }

    // Website Localization 
    public function saveSiteLocalization(Request $request){
        
        try{

            SiteLanguage::create(['key' => $request->key,
                                   'en' => $request->en,
                                   'it' => $request->it
                               ]);

            $this->updateLanguageFiles();
            
            $message = "Language created successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e){
            dd($e->getMessage());
        }
        
        
    }
    // Website Localization 
    public function editSiteLocalization(Request $request, $id){
        
        $language = SiteLanguage::find($id);
        
        return view('user::localization.edit', compact('language'));
    }

    public function updateSiteLocalization(Request $request, $id){
        try{

            SiteLanguage::where('id',$id)->update(['key' => $request->key,
                                                   'en' => $request->en,
                                                   'it' => $request->it
                                               ]);

            $this->updateLanguageFiles();
            $message = "Language changed successfuly";
            return redirect()->back()->with('success', $message); 

        }catch(\Exception $e){
            dd($e->getMessage());
        }
    }

    public function updateLanguageFiles(){
        
        $languages = SiteLanguage::get()->toArray();

        $en = [];
        $it = [];
        foreach($languages as $key => $value){
            $en[$value['key']] = $value['en'];
            $it[$value['key']] = $value['it'];
        }

        $fileEn =base_path('resources/lang/en/messages.php');        
        $enStr = "<?php"."\n"; 
        $enStr .= "return [";
        
        foreach($en as $key => $value){
            $enStr .= '"'.$key.'" => "'.$value.'",'."\n";
        }

        $enStr .= "\n"."];";
        file_put_contents($fileEn, $enStr);

        $fileIt =base_path('resources/lang/it/messages.php');        

        $itStr = "<?php"."\n"; 
        $itStr .= "return [";
        
        foreach($it as $key => $value){
            $itStr .= '"'.$key.'" => "'.$value.'",'."\n";
        }

        $itStr .= "\n"."];";
        file_put_contents($fileIt, $itStr);

        $message = "Language changed successfuly";
        return redirect()->back()->with('success', $message); 
    }


    public function deleteLocalization($id){
        $medal = SiteLanguage::where('id',$id)->delete();
        if($medal){
            $this->updateLanguageFiles();
            $message = "Deleted successfuly";
            return redirect()->back()->with('success', $message);
        }
        else{
            $message = "Something went wrong";
            return redirect()->back()->with('error', $message);
        }
    }

}
