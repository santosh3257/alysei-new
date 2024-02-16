<?php

namespace Modules\User\Http\Controllers;
use Validator;
use Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
    	if(Auth::check())
        {
            return redirect('dashboard');
        }
    	return view('auth.login');
    }

    /***
    login check
    ***/
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required', 
            'password' => 'required',
        ]);

        if ($validator->fails()) 
        { 
            return redirect()->back()->with('err_message',$validator->messages()->first());
        }

        if(Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
                'role_id' => 1,
            ]))
        {
            return redirect('dashboard');
        } 
        else
        {
            return redirect()->back()->with('err_message','Invalid email or password');
        }
        
    }
}
