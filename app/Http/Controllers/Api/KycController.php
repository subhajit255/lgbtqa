<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/kyc/verify",
     *     summary="Submit KYC Verification Details",
     *     description="Allows an authenticated user to submit their KYC details, including Govt ID, Identity Image, and their requested Badge Style/Color.",
     *     tags={"KYC Verification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"govt_id_image", "identity_image", "badge_style_id", "badge_color_id"},
     *                 @OA\Property(property="govt_id_image", type="string", format="binary", description="Government ID Image (max 100MB, format: jpeg/png/webp)"),
     *                 @OA\Property(property="identity_image", type="string", format="binary", description="Identity/Selfie Image (max 100MB, format: jpeg/png/webp)"),
     *                 @OA\Property(property="badge_style_id", type="integer", description="ID of the desired Badge Style"),
     *                 @OA\Property(property="badge_color_id", type="integer", description="ID of the desired Badge Color")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful submission",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="KYC verification details submitted successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Already submitted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You already have a pending KYC verification.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The govt id image field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function submitKyc(Request $request)
    {
        $user = auth()->user();

        // Check if user already has an existing KYC record (whether pending, approved, or rejected)
        $existingKyc = KycVerification::where('user_id', $user->id)->first();

        $validator = Validator::make($request->all(), [
            'govt_id_image' => $existingKyc ? 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400' : 'required|image|mimes:jpeg,png,jpg,webp|max:102400',
            'identity_image' => $existingKyc ? 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400' : 'required|image|mimes:jpeg,png,jpg,webp|max:102400',
            'badge_style_id' => 'required|exists:badge_styles,id',
            'badge_color_id' => 'required|exists:badge_colors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $govtIdImageName = $existingKyc ? $existingKyc->govt_id_image : null;
        if ($request->hasFile('govt_id_image')) {
            // Delete old file if exists
            if ($existingKyc && $existingKyc->govt_id_image) {
                $oldPath = storage_path('app/public/kyc/' . $existingKyc->govt_id_image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $image = $request->file('govt_id_image');
            $govtIdImageName = time() . '_govt_' . uniqid() . '.' . $image->getClientOriginalExtension();
            if (!file_exists(storage_path('app/public/kyc'))) {
                mkdir(storage_path('app/public/kyc'), 0777, true);
            }
            $image->move(storage_path('app/public/kyc'), $govtIdImageName);
        }

        $identityImageName = $existingKyc ? $existingKyc->identity_image : null;
        if ($request->hasFile('identity_image')) {
            // Delete old file if exists
            if ($existingKyc && $existingKyc->identity_image) {
                $oldPath = storage_path('app/public/kyc/' . $existingKyc->identity_image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $image = $request->file('identity_image');
            $identityImageName = time() . '_identity_' . uniqid() . '.' . $image->getClientOriginalExtension();
            if (!file_exists(storage_path('app/public/kyc'))) {
                mkdir(storage_path('app/public/kyc'), 0777, true);
            }
            $image->move(storage_path('app/public/kyc'), $identityImageName);
        }

        if ($existingKyc) {
            $existingKyc->update([
                'govt_id_image' => $govtIdImageName,
                'identity_image' => $identityImageName,
                'badge_style_id' => $request->badge_style_id,
                'badge_color_id' => $request->badge_color_id,
                'status' => 'pending',
            ]);
            $kyc = $existingKyc;

            // Create Admin Notification
            Notification::create([
                'for' => 1,
                'title' => 'KYC Verification Update Request',
                'description' => $user->name . ' has updated their KYC verification request.',
                'is_read' => 0,
            ]);
        } else {
            $kyc = KycVerification::create([
                'user_id' => $user->id,
                'govt_id_image' => $govtIdImageName,
                'identity_image' => $identityImageName,
                'badge_style_id' => $request->badge_style_id,
                'badge_color_id' => $request->badge_color_id,
                'status' => 'pending',
            ]);

            // Create Admin Notification
            Notification::create([
                'for' => 1,
                'title' => 'New KYC Verification Request',
                'description' => $user->name . ' has submitted a new KYC verification request.',
                'is_read' => 0,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'KYC verification details submitted successfully.',
            'data' => $kyc,
        ], 200);
    }
}
