<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use Hash;

  

class AuthController extends Controller
{

    /**
     *
     * Login Page
     *
     * @return response()
     *
     */
    public function index()
    {
        return view('auth.login');
    }  

      
    /**
     *
     * Registration Page
     *
     * @return response()
     *
     */
    public function registration()
    {
        return view('auth.registration');
    }

    /**
     * Login User
     *
     * @return response()
     */
    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('quiz-history')->withSuccess('You have Successfully loggedin');
        }
        return redirect("login")->withSuccess('Oppes! You have entered invalid credentials');
    }

    /**
     * Register User
     *
     * @return response()
     */
    public function postRegistration(Request $request)
    {  
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('Great! You have Successfully loggedin');

    }

    /**
     * Dashboard
     *
     * @return response()
     */
    public function dashboard()
    {
        if(Auth::check()){
            return view('dashboard');
        }
        return redirect("login")->withSuccess('Opps! You do not have access');
    }

    /**
     * Create User
     *
     * @return response()
     */
    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);

    }

    /**
     * Logout User
     *
     * @return response()
     */
    public function logout() {
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }

}

