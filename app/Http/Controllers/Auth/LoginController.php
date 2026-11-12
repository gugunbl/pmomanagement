<?php

namespace App\Http\Controllers\Auth;

use anlutro\LaravelSettings\Facade as Setting;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

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
    }

    public function maxAttempts()
    {
        return (int) Setting::get('max_login_attempts', 3);
    }

    public function decayMinutes()
    {
        return (int) Setting::get('lockout_delay', 2);
    }

    public function login(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            flash('You are locked! Too many attempts. please try '.setting('lockout_delay').' mintutes later.')->warning();

            return redirect()->route('login')->withInput();
        }

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();
        $authenticated = false;

        if ($user) {
            try {
                // Try to decrypt the password
                $decryptedPassword = Crypt::decryptString($user->password);
                if ($decryptedPassword === $credentials['password']) {
                    Auth::login($user, $request->remember);
                    $authenticated = true;
                }
            } catch (\Exception $e) {
                // If decryption fails, it might be an old hashed password or something else
                if (Hash::check($credentials['password'], $user->password)) {
                    Auth::login($user, $request->remember);
                    $authenticated = true;
                }
            }
        }

        if ($authenticated) {
            $userStatus = Auth::User()->status;
            if ($userStatus == 1) {
                if (Auth::user()->can('menu-dashboard')) {
                    return redirect()->intended(url('/home'));
                } else {
                    return redirect()->intended(url('/home_user'));
                }
            } else {
                Auth::logout();
                flash('You are temporary blocked. please contact to admin')->warning();

                return redirect()->route('login')->withInput();
            }
        } else {
            $this->incrementLoginAttempts($request);
            flash('Incorrect username or password. Please try again')->error();

            return redirect()->route('login')->withInput();
        }
    }
}
