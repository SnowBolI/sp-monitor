<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Key;
use App\Models\Jabatan;
use App\Models\VerifyUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\UserToken;
use Exception;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index(){
        $title = "Login";
        return view('auth.login', compact('title'));
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postLogin(Request $request)
    {
        // Log::info('postLogin method called');

        // $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // Log::info('Request validated', ['email' => $request->email]);

        // $credentials = $request->only('email', 'password');

        // // Check if user exists
        // $user = User::where('email', $request->email)->first();
        // if (!$user) {
        //     Log::warning('User not found', ['email' => $request->email]);
        //     return back()->withInput()->withErrors([
        //         'message' => 'The provided credentials do not match our records (User not found).',
        //     ]);
        // }

        // Log::info('User found', ['email' => $request->email, 'user_id' => $user->id]);

        // // Check if the password matches
        // if ($request->password !== $user->password) {
        //     Log::warning('Password mismatch', ['email' => $request->email]);
        //     return back()->withInput()->withErrors([
        //         'message' => 'The provided credentials do not match our records (Password mismatch).',
        //     ]);
        // }

        // Log::info('Password matches', ['email' => $request->email]);

        // // Attempt to authenticate the user
        // if (Auth::attempt($credentials)) {
        //     Log::info('User authenticated successfully', ['user_id' => $user->id]);
        //     return redirect()->intended('dashboard');
        // } else {
        //     Log::warning('Authentication failed', ['email' => $request->email]);
        //     return back()->withInput()->withErrors([
        //         'message' => 'The provided credentials do not match our records.',
        //     ]);
        // }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)
                ->join('statuses', 'users.status', '=', 'statuses.id')
                ->select('users.*', 'status as status_name')
                ->first();

    if ($user) {
        if ($user->status != 1) {
            $message = $user->status === null ? 'Status akun tidak valid.' : 'Akun tidak aktif.';
            return back()->withInput()->withErrors([
                'email' => $message . ' Silakan hubungi administrator.',
            ]);
        }
    }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            Log::debug('Login', ['jabatan' => $user->jabatan_id]);
            switch ($user->jabatan_id) {
                case '1':
                    return redirect()->intended(route('direksi.dashboard'));
                    case '2':
                        return redirect()->intended(route('kepala-cabang.dashboard'));
                        case '3':
                            return redirect()->intended(route('supervisor.dashboard'));
                         case '4':
                                return redirect()->intended(route('admin-kas.dashboard'));   
                                case '5':
                                    return redirect()->intended(route('account-officer.dashboard'));
                                    case '6':
                                        return redirect()->intended(route('kepala-bagian.dashboard'));
                                        case '99':
                                            return redirect()->intended(route('super-admin.dashboard'));

                // dst ...

                default:
                    // Error jabatan tidak valid
                    abort(404);
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }


    /**
     * Write code on Method
     *
     * @return response()
     */

     public function Register()
     {
         $title = "Register";
         $jabatans = Jabatan::all(); // Ambil data jabatan dari database
         return view('auth.register', compact('title', 'jabatans')); // Kirim variabel ke view
     }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function postRegister(Request $request)
{
    // Log the incoming request data
    Log::info('Register request received', $request->all());

    try {
        // Validate registration data
        $validated = $request->validate([
            'name'             => 'required|max:255',
            'email'            => 'required|email|unique:users|max:255',
            'key'              => 'required|exists:keys,key',
            'password'         => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        // Log validated data
        Log::info('Validated registration data', $validated);

        // Get the jabatan_id from the key
        $key = Key::where('key', $validated['key'])->first();

        // Save new user data
        $user = new User;
        $user->name        = $validated['name'];
        $user->email       = $validated['email'];
        $user->key        = $key->key;
        $user->jabatan_id  = $key->jabatan;
        $user->password    = Hash::make($validated['password']);
        $user->status      = 2;
        $user->save();

        // Log the newly created user
        Log::info('New user created', ['user_id' => $user->id]);

        // Generate a token
        $token = Str::random(64);
        UserToken::create([
            'user_id' => $user->id,
            'token'   => $token,
        ]);

        // Log token generation
        Log::info('Token generated', ['user_id' => $user->id, 'token' => $token]);

        // Send verification email
        Mail::send('emails.verify_email', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Email Verification Mail');
        });

        // Log email sending
        Log::info('Verification email sent', ['to' => $request->email]);

        return redirect("register")->withSuccess('Verifikasi Email telah dikirim ke ' . $request->email . ', mohon klik link di email untuk verifikasi Email anda.');
    } catch (Exception $e) {
        // Log the error and exception
        Log::error('Error during registration', [
            'message'   => $e->getMessage(),
            'trace'     => $e->getTraceAsString(),
            'request'   => $request->all(),
            'code'      => $e->getCode()
        ]);

        return redirect("register")->withErrors([
            'error'     => 'Some error occurred, please try later',
            'exception' => $e->getMessage()
        ]);
    }
}
    // public function postRegister(Request $request)
    // {
    //     // Log the incoming request data
    //     Log::info('Register request received', $request->all());

    //     try {
    //         // Validasi data pendaftaran
    //         $validated = $request->validate([
    //             'name'             => 'required|max:255',
    //             'email'            => 'required|email|unique:users|max:255',
    //             'nip'              => 'required',
    //             'password'         => 'required|min:6',
    //             'confirm_password' => 'required|same:password',
    //         ]);

    //         // Log validated data
    //         Log::info('Validated registration data', $validated);

    //         // Simpan data pengguna baru
    //         $user = new User;
    //         $user->name        = $validated['name'];
    //         $user->email       = $validated['email'];
    //         $user->nip         = $validated['nip'];
    //         $user->password    = Hash::make($validated['password']);
    //         $user->save();

    //         // Log the newly created user
    //         Log::info('New user created', ['user_id' => $user->id]);

    //         // Generate a token
    //         $token = Str::random(64);
    //         UserToken::create([
    //             'user_id' => $user->id,
    //             'token'   => $token,
    //         ]);

    //         // Log token generation
    //         Log::info('Token generated', ['user_id' => $user->id, 'token' => $token]);

    //         // Send verification email
    //         Mail::send('emails.verify_email', ['token' => $token], function ($message) use ($request) {
    //             $message->to($request->email);
    //             $message->subject('Email Verification Mail');
    //         });

    //         // Log email sending
    //         Log::info('Verification email sent', ['to' => $request->email]);

    //         return redirect("register")->withSuccess('A verification email is sent to ' . $request->email . ', please click the link in the email to verify your email.');
    //     } catch (Exception $e) {
    //         // Log the error and exception
    //         Log::error('Error during registration', [
    //             'message'   => $e->getMessage(),
    //             'trace'     => $e->getTraceAsString(),
    //             'request'   => $request->all(),
    //             'code'      => $e->getCode()
    //         ]);

    //         return redirect("register")->withErrors([
    //             'error'     => 'Some error occurred, please try later',
    //             'exception' => $e->getMessage()
    //         ]);
    //     }
    // }
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function verifyEmail($token)
    {
        $verifyUser = UserToken::where('token', $token)->first();
        if (!is_null($verifyUser)) {
            $verifyUser->user->email_verified_at = Carbon::now();
            $verifyUser->user->save();

            // delete token
            $verifyUser->delete();
            $user = User::find($verifyUser->user_id);
            Auth::login($user);
            $message = "Email verified Successfully";

            session(['user_name' => $user->name, 'user_id' => $user->id, 'user_email' => $user->email]);
            return redirect('register')->withSuccess($message);
        } else {
            $message = 'Token Error: Email can not be verified.';
            return redirect()->route('page.error')->withError($message);
        }
    }

    public function showErrorPage()
    {
        $title = "Error";
        return view('error_page', compact('title'));
    }
    public function changePasswordForm()
    {
        $title = "Change Password";
        return view('Auth.change_password', compact('title'));
    }

    public function changePasswordPost(Request $request)
    {
        $request->validate([
            'current_password'  => 'required',
            'new_password'      => 'required|min:6',
            'confirm_password' => 'required|same:new_password|min:6',
        ]);

        if (!(Hash::check($request->current_password, Auth::user()->password))) {

            return redirect()->back()->with("error", "Your current password does not match with the password you entered.");
        }

        if (strcmp($request->current_password, $request->new_password) == 0) {
            // Current password and new password same
            return redirect()->back()->with("error", "New Password cannot be same as your current password.");
        }

        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->new_password);
        $user->save();
        return redirect()->back()->with("success", "Password successfully changed!");
    }

    public function forgetPasswordForm()
    {
        $title = "Forgot Password";
        return view('auth.forget_password', compact('title'));
    }

    public function forgetPasswordPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(64);

        PasswordReset::updateOrCreate([
            'email' => $request->email,
            ],
            [
            'token' => $token,
            ]);

        Mail::send('emails.forget_password', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return back()->with('message', 'We have e-mailed your password reset link!');
    }

    public function resetPasswordForm($token)
    {
        $title = "Reset Password";
        return view('auth.reset_password_form', ['token' => $token], compact('title'));
    }

    public function resetPasswordPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'new_password'      => 'required|min:6',
            'confirm_password' => 'required|same:new_password|min:6',
        ]);

        $verifyToken = DB::table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if (!$verifyToken) {
            return back()->withInput()->with('error', 'Invalid Token!');
        }

        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->new_password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return redirect('login')->with('message', 'Your password is Reset. Please login with new password!');
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect('/');
    }
}
