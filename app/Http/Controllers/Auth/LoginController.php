<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use anlutro\LaravelSettings\Facade as Setting;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    protected $maxAttempts;
    protected $decayMinutes;

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->maxAttempts = Setting::get('max_login_attempts', 3);
        $this->decayMinutes = Setting::get('lockout_delay', 2);
    }


    public function login(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            flash('You are locked! Too many attempts. please try '. setting('lockout_delay') .' mintutes later.')->warning();
            return redirect()->route('login')->withInput();
        }

        $credentials = $request->only('email', 'password');
        $user = \App\User::where('email', $credentials['email'])->first();
        $authenticated = false;

        if ($user) {
            try {
                // Try to decrypt the password
                $decryptedPassword = \Illuminate\Support\Facades\Crypt::decryptString($user->password);
                if ($decryptedPassword === $credentials['password']) {
                    Auth::login($user, $request->remember);
                    $authenticated = true;
                }
            } catch (\Exception $e) {
                // If decryption fails, it might be an old hashed password or something else
                if (\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
                    Auth::login($user, $request->remember);
                    $authenticated = true;
                }
            }
        }

        if($authenticated){
            $userStatus = Auth::User()->status;
            if($userStatus==1) {
                if(Auth::user()->can('menu-dashboard')) {
                    return redirect()->intended(url('/home'));
                } else {
                    return redirect()->intended(url('/home_user'));
                }
            }else{
                Auth::logout();
                flash('You are temporary blocked. please contact to admin')->warning();
                return redirect()->route('login')->withInput();
            }
        }
        else {
            $this->incrementLoginAttempts($request);
            flash('Incorrect username or password. Please try again')->error();
            return redirect()->route('login')->withInput();
        }
    }


}
