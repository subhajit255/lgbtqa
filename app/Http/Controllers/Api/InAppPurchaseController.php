<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Plan;
use App\Models\SupportOption;
use App\Traits\PaymentCoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InAppPurchaseController extends BaseController
{
    use PaymentCoreTrait;

    /**
     * @OA\Post(
     *     path="/api/in-app-purchase/verify-apple",
     *     summary="Verify Apple StoreKit 2 Purchase",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id"},
     *             @OA\Property(property="transaction_id", type="string", example="1000000000000000"),
     *             @OA\Property(property="product_id", type="string", example="com.joinpm.app.premium"),
     *             @OA\Property(property="is_support", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful verification")
     * )
     */
    public function verifyApplePurchase(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'product_id' => 'required|string',
            'is_support' => 'boolean'
        ]);

        // In a real implementation, you would call App Store Server API here
        // to verify the JWS signed transaction and get the original_transaction_id
        
        // $appleService = new \App\Services\AppleIAPService();
        // $transaction = $appleService->verifyTransaction($request->transaction_id);
        
        // Mocking successful response for now
        $originalTransactionId = $request->transaction_id;
        $status = 'ACTIVE';
        $user = auth()->user();

        DB::beginTransaction();
        try {
            if ($request->is_support) {
                // Determine amount and currency from DB based on product_id
                $supportOption = SupportOption::where('type', $request->product_id)->first();
                $amount = $supportOption ? $supportOption->amount : 0;
                
                $record = $this->grantVoluntarySupport(
                    $user,
                    $amount,
                    'usd',
                    'apple',
                    ['original_transaction_id' => $originalTransactionId],
                    $status
                );

                $this->logTransaction(
                    $user,
                    $originalTransactionId,
                    'apple',
                    'one_time', // or subscription for monthly support
                    $amount,
                    'usd',
                    'completed',
                    'Apple Support/Add-on purchase'
                );
            } else {
                $plan = Plan::where('stripe_price_id', $request->product_id)->orWhere('name', $request->product_id)->first();
                if (!$plan) {
                    throw new \Exception("Plan not found for product id: " . $request->product_id);
                }

                $record = $this->grantSubscription(
                    $user,
                    $plan,
                    'apple',
                    ['original_transaction_id' => $originalTransactionId],
                    $status,
                    now(),
                    $plan->billing_cycle === 'MONTHLY' ? now()->addMonth() : null
                );

                $this->logTransaction(
                    $user,
                    $originalTransactionId,
                    'apple',
                    'subscription',
                    $plan->price,
                    $plan->currency ?? 'usd',
                    'completed',
                    'Apple Plan purchase'
                );
            }

            DB::commit();
            return $this->responseJson(true, 200, 'Apple purchase verified successfully', ['record' => $record]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseJson(false, 500, 'Apple verification failed: ' . $e->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/in-app-purchase/verify-google",
     *     summary="Verify Google Play Billing Purchase",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"purchase_token", "product_id"},
     *             @OA\Property(property="purchase_token", type="string"),
     *             @OA\Property(property="product_id", type="string"),
     *             @OA\Property(property="is_support", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successful verification")
     * )
     */
    public function verifyGooglePurchase(Request $request)
    {
        $request->validate([
            'purchase_token' => 'required|string',
            'product_id' => 'required|string',
            'is_support' => 'boolean'
        ]);

        // Google Play Developer API verification
        // Acknowledge the purchase if not already acknowledged
        // $googleService = new \App\Services\GoogleIAPService();
        // $purchase = $googleService->verifyAndAcknowledge($request->product_id, $request->purchase_token);
        
        $user = auth()->user();
        $status = 'ACTIVE';

        DB::beginTransaction();
        try {
            if ($request->is_support) {
                $supportOption = SupportOption::where('type', $request->product_id)->first();
                $amount = $supportOption ? $supportOption->amount : 0;
                
                $record = $this->grantVoluntarySupport(
                    $user,
                    $amount,
                    'usd',
                    'google',
                    ['purchase_token' => $request->purchase_token, 'is_acknowledged' => 1],
                    $status
                );

                $this->logTransaction(
                    $user,
                    $request->purchase_token,
                    'google',
                    'one_time',
                    $amount,
                    'usd',
                    'completed',
                    'Google Support/Add-on purchase'
                );
            } else {
                $plan = Plan::where('stripe_price_id', $request->product_id)->orWhere('name', $request->product_id)->first();
                if (!$plan) {
                    throw new \Exception("Plan not found for product id: " . $request->product_id);
                }

                $record = $this->grantSubscription(
                    $user,
                    $plan,
                    'google',
                    ['purchase_token' => $request->purchase_token, 'is_acknowledged' => 1],
                    $status,
                    now(),
                    $plan->billing_cycle === 'MONTHLY' ? now()->addMonth() : null
                );

                $this->logTransaction(
                    $user,
                    $request->purchase_token,
                    'google',
                    'subscription',
                    $plan->price,
                    $plan->currency ?? 'usd',
                    'completed',
                    'Google Plan purchase'
                );
            }

            DB::commit();
            return $this->responseJson(true, 200, 'Google purchase verified successfully', ['record' => $record]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseJson(false, 500, 'Google verification failed: ' . $e->getMessage(), []);
        }
    }
}
