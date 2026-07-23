<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\User;
use App\Models\BadgeStyle;
use App\Models\BadgeColor;
use Illuminate\Http\Request;

class KycVerificationController extends Controller
{
    public function index()
    {
        $kycRequests = KycVerification::with(['user', 'badgeStyle', 'badgeColor'])->latest()->paginate(10);
        return view('admin.kyc.list', compact('kycRequests'));
    }

    public function view($id)
    {
        $kyc = KycVerification::with(['user', 'badgeStyle', 'badgeColor'])->findOrFail($id);
        return view('admin.kyc.view', compact('kyc'));
    }

    public function approve($id)
    {
        $kyc = KycVerification::findOrFail($id);
        $kyc->status = 'approved';
        $kyc->save();

        return redirect()->back()->with('success', 'KYC Verification Approved.');
    }

    public function reject($id)
    {
        $kyc = KycVerification::findOrFail($id);
        $kyc->status = 'rejected';
        $kyc->save();

        return redirect()->back()->with('success', 'KYC Verification Rejected.');
    }

    public function add()
    {
        $users = User::orderBy('name')->get();
        $badgeStyles = BadgeStyle::where('status', 1)->get();
        $badgeColors = BadgeColor::where('status', 1)->get();
        return view('admin.kyc.add', compact('users', 'badgeStyles', 'badgeColors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'govt_id_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:102400',
            'identity_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:102400',
            'badge_style_id' => 'required|exists:badge_styles,id',
            'badge_color_id' => 'required|exists:badge_colors,id',
        ]);

        $govtIdImageName = null;
        if ($request->hasFile('govt_id_image')) {
            $image = $request->file('govt_id_image');
            $govtIdImageName = time() . '_admin_govt_' . uniqid() . '.' . $image->getClientOriginalExtension();
            if (!file_exists(storage_path('app/public/kyc'))) {
                mkdir(storage_path('app/public/kyc'), 0777, true);
            }
            $image->move(storage_path('app/public/kyc'), $govtIdImageName);
        }

        $identityImageName = null;
        if ($request->hasFile('identity_image')) {
            $image = $request->file('identity_image');
            $identityImageName = time() . '_admin_identity_' . uniqid() . '.' . $image->getClientOriginalExtension();
            if (!file_exists(storage_path('app/public/kyc'))) {
                mkdir(storage_path('app/public/kyc'), 0777, true);
            }
            $image->move(storage_path('app/public/kyc'), $identityImageName);
        }

        KycVerification::create([
            'user_id' => $request->user_id,
            'govt_id_image' => $govtIdImageName,
            'identity_image' => $identityImageName,
            'badge_style_id' => $request->badge_style_id,
            'badge_color_id' => $request->badge_color_id,
            'status' => 'approved',
        ]);

        return redirect()->route('admin.kyc.list')->with('success', 'KYC Verification added successfully.');
    }
}
