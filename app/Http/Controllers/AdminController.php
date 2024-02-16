<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User; 
use App\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; 
use Validator;
//use App\Events\UserRegisterEvent;

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
        return view('admin.home');
    }

    
    /***
    logout
    ***/
    public function logout(Request $request)
    {
        Auth::logout();
        return Redirect('login');
    }

}
