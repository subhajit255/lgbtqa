<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    public function privacyPolicy()
    {
        $details = Setting::find(1);
        $content = $details->privacy_policy ?? 'Privacy Policy content not available.';
        $title = 'Privacy Policy';
        return view('policies.privacy', compact('content', 'title'));
    }

    public function childSafety()
    {
        $details = Setting::find(1);
        $content = $details->child_safety ?? 'Child Safety content not available.';
        $title = 'Child Safety';
        return view('policies.child_safety', compact('content', 'title'));
    }

    public function termsAndConditions()
    {
        $details = Setting::find(1);
        $content = $details->term_and_condition ?? 'Terms & Conditions content not available.';
        $title = 'Terms & Conditions';
        return view('policies.terms', compact('content', 'title'));
    }

    public function deleteAccount()
    {
        $title = 'Delete Account';
        return view('auth.delete_account', compact('title'));
    }

    public function processDeleteAccount(Request $request)
    {
        $request->validate([
            'identity' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->identity)
                    ->orWhere('mobile_number', $request->identity)
                    ->first();

        if ($user) {
            return redirect()->back()->with('success', 'User Deleted Successfully');
        }

        return redirect()->back()->with('error', 'Aww !!! User Not Found, Please check your email or phone number');
    }
}
