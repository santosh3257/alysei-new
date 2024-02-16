<?php namespace App\Providers;

use View;
use Illuminate\Support\ServiceProvider;
use DB;
class ComposerServiceProvider extends ServiceProvider {

    public function boot()
    {
        View::composer('*', function($view){
            
            $new_stores_count = DB::table('marketplace_stores')
                                    ->where('status','0')
                                    ->whereNull('deleted_at')->count();
            $new_spams_count = DB::table('activity_spams')
                                    ->where('status','0')
                                    ->count();

            $view->with('new_stores_count', $new_stores_count)->with('new_spams_count',$new_spams_count);
        });
    }


    public function register()
    {
        //
    }
}