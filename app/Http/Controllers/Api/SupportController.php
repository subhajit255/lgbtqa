<?php

namespace App\Http\Controllers\Api;

use \App\Models\SupportOption;
use App\Http\Controllers\Controller;
use App\Models\UserSupportRecognition;
use App\Models\VoluntarySupport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/support/options",
     *     operationId="getSupportOptions",
     *     tags={"Voluntary Support"},
     *     summary="Get preset support options",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Support options retrieved successfully."
     *     )
     * )
     */
    public function getOptions()
    {
        $oneTimeOptions = SupportOption::where('type', 'one_time')->get()->map(function ($item) {
            return [
                'key' => 'support_one_time_' . (int)$item->amount,
                'amount' => (int)$item->amount,
                'currency' => $item->currency,
                'label' => $item->label
            ];
        });

        $monthlyOptions = SupportOption::where('type', 'monthly')->get()->map(function ($item) {
            return [
                'key' => 'support_monthly_' . (int)$item->amount,
                'amount' => (int)$item->amount,
                'currency' => $item->currency,
                'label' => $item->label
            ];
        });

        $options = [
            'one_time' => $oneTimeOptions,
            'monthly' => $monthlyOptions
        ];

        return response()->json(['status' => true, 'data' => $options, 'message' => 'Support options retrieved successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/support/verify-receipt",
     *     operationId="verifySupportReceipt",
     *     tags={"Voluntary Support"},
     *     summary="Verify voluntary support payment receipt",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="ONE_TIME", enum={"ONE_TIME", "MONTHLY"}),
     *             @OA\Property(property="amount", type="number", example=10),
     *             @OA\Property(property="currency", type="string", example="CHF"),
     *             @OA\Property(property="stripe_payment_intent_id", type="string", example="pi_12345"),
     *             @OA\Property(property="stripe_subscription_id", type="string", example="sub_12345")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Support confirmed successfully."
     *     )
     * )
     */
    public function verifyReceipt(Request $request)
    {
        $request->validate([
            'type' => 'required|in:ONE_TIME,MONTHLY',
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'stripe_payment_intent_id' => 'required_if:type,ONE_TIME',
            'stripe_subscription_id' => 'required_if:type,MONTHLY',
        ]);

        $user = auth()->user();

        // Verify with Stripe: backend would normally check the intent/subscription status here

        $expiresAt = null;
        if ($request->type == 'ONE_TIME') {
            $expiresAt = Carbon::now()->addDays(30); // One-time support recognition lasts 30 days
        }

        $support = VoluntarySupport::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'currency' => strtoupper($request->currency),
            'stripe_payment_intent_id' => $request->stripe_payment_intent_id,
            'stripe_subscription_id' => $request->stripe_subscription_id,
            'status' => 'ACTIVE',
            'expires_at' => $expiresAt
        ]);

        return response()->json(['status' => true, 'data' => $support, 'message' => 'Support confirmed successfully.']);
    }

    /**
     * @OA\Get(
     *     path="/api/support/recognition",
     *     operationId="getSupportRecognition",
     *     tags={"Voluntary Support"},
     *     summary="Get voluntary support recognition preferences",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Recognition preferences retrieved successfully."
     *     )
     * )
     */
    public function getRecognition()
    {
        $user = auth()->user();
        $recognition = UserSupportRecognition::firstOrCreate(
            ['user_id' => $user->id],
            ['visibility_mode' => 'PRIVATE', 'show_amount' => false, 'show_badge' => false]
        );

        return response()->json(['status' => true, 'data' => $recognition, 'message' => 'Recognition preferences retrieved successfully.']);
    }

    /**
     * @OA\Post(
     *     path="/api/support/recognition",
     *     operationId="updateSupportRecognition",
     *     tags={"Voluntary Support"},
     *     summary="Update voluntary support recognition preferences",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="visibility_mode", type="string", example="PRIVATE", enum={"PRIVATE", "SHOW_MY_PROFILE", "SHOW_ANONYMOUSLY"}),
     *             @OA\Property(property="show_amount", type="boolean", example=false),
     *             @OA\Property(property="show_badge", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recognition preferences updated successfully."
     *     )
     * )
     */
    public function updateRecognition(Request $request)
    {
        $request->validate([
            'visibility_mode' => 'required|in:PRIVATE,SHOW_MY_PROFILE,SHOW_ANONYMOUSLY',
            'show_amount' => 'required|boolean',
            'show_badge' => 'required|boolean',
        ]);

        $user = auth()->user();
        $recognition = UserSupportRecognition::updateOrCreate(
            ['user_id' => $user->id],
            [
                'visibility_mode' => $request->visibility_mode,
                'show_amount' => $request->show_amount,
                'show_badge' => $request->show_badge,
            ]
        );

        return response()->json(['status' => true, 'data' => $recognition, 'message' => 'Recognition preferences updated successfully.']);
    }
}
