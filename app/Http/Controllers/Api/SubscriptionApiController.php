<?php

namespace App\Http\Controllers\Api;

use \App\Models\SupportOption;
use \App\Models\Transaction;
use \App\Models\VoluntarySupport;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Str;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Api\SubscriptionCollection;
use App\Http\Resources\Api\User\ProfileResource;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class SubscriptionApiController extends BaseController
{
    use \App\Traits\StripeHelper;

    /**
     * @OA\Get(
     *     path="/api/subscription/plans",
     *     operationId="getPlans",
     *     tags={"Subscriptions"},
     *     summary="Get all active subscription plans",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getPlans()
    {
        $plans = Plan::where('is_active', 1)->get();
        return response()->json([
            'status' => true,
            'data' => $plans
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/subscription/add-ons",
     *     operationId="getAddOns",
     *     tags={"Subscriptions"},
     *     summary="Get all one-time add-ons",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getAddOns()
    {
        $addons = SupportOption::where('type', 'add_on')->get();
        return response()->json([
            'status' => true,
            'data' => $addons
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/subscription/status",
     *     operationId="getSubscriptionStatus",
     *     tags={"Subscriptions"},
     *     summary="Get current user subscription status",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function status(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription()->with('plan')->first();

        return $this->responseJson(true, 200, 'Subscription status fetched successfully', [
            'has_active_subscription' => $subscription ? true : false,
            'subscription' => $subscription
        ]);
    }
    /**
     * @OA\Get(
     *     path="/api/subscription/my-current-subscription",
     *     operationId="myCurrentSubscription",
     *     tags={"Subscriptions"},
     *     summary="Get current active subscription details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function myCurrentSubscription(Request $request)
    {
        try {
            $subscription = UserSubscription::where(['user_id' => auth()->user()->id, 'status' => 'ACTIVE'])->latest()->first();
            return $this->responseJson(true, 200, 'Subscription fetched successfully', $subscription ? new SubscriptionCollection($subscription) : null);
        } catch (\Exception $e) {
            logger($e->getMessage() . '--' . $e->getLine() . '--' . $e->getFile());
            return $this->responseJson(false, 500, 'Something went wrong', []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subscription/checkout",
     *     operationId="processCheckout",
     *     tags={"Subscriptions"},
     *     summary="Process a subscription or one-time payment API-based checkout",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="plan_uuid", type="string", example="uuid-of-plan", description="Required if support_option_uuid is not provided"),
     *             @OA\Property(property="support_option_uuid", type="string", example="uuid-of-support-option", description="Required if plan_uuid is not provided (for one-time add-ons)"),
     *             @OA\Property(property="payment_method_id", type="string", example="pm_1234567890", description="Required for paid plans and add-ons"),
     *             @OA\Property(property="idempotency_key", type="string", example="optional-uuid-here", description="Optional key to strictly guarantee idempotency and prevent double-charging")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_uuid' => 'required_without:support_option_uuid|exists:plans,uuid',
            'support_option_uuid' => 'required_without:plan_uuid|exists:support_options,uuid',
            'payment_method_id' => 'nullable|string',
            'idempotency_key' => 'nullable|string'
        ]);

        $plan = null;
        $supportOption = null;
        $price = 0;
        $currency = 'usd';
        $name = '';
        $isOneTime = false;

        if ($request->plan_uuid) {
            $plan = Plan::where('uuid', $request->plan_uuid)->firstOrFail();
            $price = $plan->price;
            $currency = $plan->currency ?? 'usd';
            $name = $plan->name;
            $isOneTime = $plan->billing_cycle === 'ONE_TIME';
        } else {
            $supportOption = SupportOption::where('uuid', $request->support_option_uuid)->firstOrFail();
            $price = $supportOption->amount;
            $currency = $supportOption->currency ?? 'usd';
            $name = $supportOption->label ?? $supportOption->type;
            $isOneTime = true; // Add-ons are always one-time
        }

        $user = auth()->user();

        // Direct purchase for amount 0
        if ($price == 0) {
            if ($plan) {
                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => 'ACTIVE',
                    'starts_at' => now(),
                    'expires_at' => $isOneTime ? null : ($plan->billing_cycle === 'MONTHLY' ? now()->addMonth() : null),
                    'auto_renew' => !$isOneTime,
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => $subscription->id,
                    'payment_type' => 'free',
                    'payment_method_id' => null,
                    'amount' => 0,
                    'currency' => config('services.stripe.currency', 'usd'),
                    'payment_status' => 'completed',
                    'description' => 'Free plan activation: ' . $name,
                ]);

                return $this->responseJson(true, 200, 'Plan activated successfully.', [
                    'subscription' => $subscription
                ]);
            } else {
                $support = VoluntarySupport::create([
                    'user_id' => $user->id,
                    'type' => 'ONE_TIME',
                    'amount' => 0,
                    'currency' => config('services.stripe.currency', 'usd'),
                    'status' => 'ACTIVE',
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => $support->id,
                    'payment_type' => 'free',
                    'payment_method_id' => null,
                    'amount' => 0,
                    'currency' => config('services.stripe.currency', 'usd'),
                    'payment_status' => 'completed',
                    'description' => 'Free add-on activation: ' . $name,
                ]);

                return $this->responseJson(true, 200, 'Add-on activated successfully.', [
                    'support' => $support
                ]);
            }
        }

        if (!$request->payment_method_id) {
            return $this->responseJson(false, 400, 'Payment method is required for paid purchases.', []);
        }

        if ($plan && !$plan->stripe_price_id && !$isOneTime) {
            return $this->responseJson(false, 400, 'This plan cannot be purchased via Stripe right now.', []);
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $idempotencyKey = $request->idempotency_key ?? Str::uuid()->toString();

        DB::beginTransaction();

        try {
            if (!$user->stripe_customer_id) {
                // Create customer with payment method
                $customer = $this->createCustomer($user->email, $user->name, [
                    'payment_method' => $request->payment_method_id,
                    'invoice_settings' => ['default_payment_method' => $request->payment_method_id],
                ]);

                $user->stripe_customer_id = $customer->id;
                $user->save();
            } else {
                // Customer exists, attach payment method
                $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
                $paymentMethod->attach(['customer' => $user->stripe_customer_id]);

                \Stripe\Customer::update($user->stripe_customer_id, [
                    'invoice_settings' => ['default_payment_method' => $request->payment_method_id]
                ]);
            }

            if ($isOneTime) {
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => (int)($price * 100),
                    'currency' => strtolower($currency),
                    'customer' => $user->stripe_customer_id,
                    'payment_method' => $request->payment_method_id,
                    'off_session' => true,
                    'confirm' => true,
                    'description' => "One-time payment for {$name}",
                ], ['idempotency_key' => $idempotencyKey]);

                if ($paymentIntent->status !== 'succeeded') {
                    throw new \Exception("Payment failed with status: " . $paymentIntent->status);
                }

                $record = null;
                if ($plan) {
                    $record = UserSubscription::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'stripe_customer_id' => $user->stripe_customer_id,
                        'status' => 'ACTIVE',
                        'starts_at' => now(),
                        'expires_at' => null,
                        'auto_renew' => false,
                    ]);
                } else {
                    $record = VoluntarySupport::create([
                        'user_id' => $user->id,
                        'type' => 'ONE_TIME',
                        'amount' => $price,
                        'currency' => strtolower($currency),
                        'stripe_payment_intent_id' => $paymentIntent->id,
                        'status' => 'ACTIVE',
                    ]);
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => $paymentIntent->id,
                    'payment_type' => 'one_time',
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $price,
                    'currency' => strtolower($currency),
                    'payment_status' => 'completed',
                    'idempotency_key' => $idempotencyKey,
                    'description' => 'One-time payment for: ' . $name,
                ]);
            } else {
                $stripeSubscription = \Stripe\Subscription::create([
                    'customer' => $user->stripe_customer_id,
                    'items' => [
                        ['price' => $plan->stripe_price_id],
                    ],
                    'expand' => ['latest_invoice.payment_intent'],
                ], ['idempotency_key' => $idempotencyKey]);

                $isActive = in_array($stripeSubscription->status, ['active', 'trialing']);

                if ($isActive) {
                    UserSubscription::where('user_id', $user->id)->update(['status' => 'CANCELLED']);
                }

                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'stripe_subscription_id' => $stripeSubscription->id,
                    'stripe_customer_id' => $user->stripe_customer_id,
                    'status' => $isActive ? 'ACTIVE' : 'PENDING',
                    'starts_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start ?? $stripeSubscription->items->data[0]->current_period_start ?? $stripeSubscription->start_date),
                    'expires_at' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end ?? $stripeSubscription->items->data[0]->current_period_end),
                    'auto_renew' => true,
                ]);

                $paymentIntentId = null;
                if (isset($stripeSubscription->latest_invoice->payment_intent)) {
                    $paymentIntentId = $stripeSubscription->latest_invoice->payment_intent->id;
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => $paymentIntentId ?? $stripeSubscription->id,
                    'payment_type' => 'subscription',
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $plan->price,
                    'currency' => strtolower($plan->currency ?? 'usd'),
                    'payment_status' => $isActive ? 'completed' : 'pending',
                    'idempotency_key' => $idempotencyKey,
                    'description' => 'Subscription purchase: ' . $plan->name,
                ]);
            }

            DB::commit();

            return $this->responseJson(true, 200, $plan ? 'Subscription successful' : 'Add-on purchase successful', [
                'record' => $plan ? $subscription : $record
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            DB::rollBack();

            try {
                Transaction::create([
                    'user_id' => auth()->id(),
                    'payment_type' => $isOneTime ? 'one_time' : 'subscription',
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $price,
                    'currency' => strtolower($currency),
                    'payment_status' => 'failed',
                    'idempotency_key' => $idempotencyKey,
                    'description' => 'Failed purchase: ' . $name,
                    'payment_details' => $e->getMessage()
                ]);
            } catch (\Throwable $err) {
                // Ignore transaction logging failure
            }

            return $this->responseJson(false, 400, 'Payment failed: ' . $e->getMessage(), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            logger($th->getMessage() . '--' . $th->getLine() . '--' . $th->getFile());
            return $this->responseJson(false, 500, 'Something went wrong', []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subscription/payment-complete",
     *     operationId="paymentComplete",
     *     tags={"Subscriptions"},
     *     summary="Verify and complete a pending payment",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"transaction_id", "status"},
     *             @OA\Property(property="transaction_id", type="string", example="pi_1234567890"),
     *             @OA\Property(property="status", type="integer", example=1, description="1 for success, 2/3 for failure")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function paymentComplete(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string|exists:transactions,transaction_id',
            'status' => 'required|in:1,2,3',
        ]);

        DB::beginTransaction();

        try {
            $transaction = Transaction::where('transaction_id', $request->transaction_id)->first();
            $user = User::find($transaction->user_id);

            if (!$transaction) {
                return $this->responseJson(false, 404, 'Transaction not found.', []);
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $paymentMethodId = null;
            $isStripeSuccess = false;

            if (str_starts_with($transaction->transaction_id, 'pi_')) {
                $paymentIntent = $stripe->paymentIntents->retrieve($transaction->transaction_id);
                $paymentMethodId = $paymentIntent->payment_method;
                $isStripeSuccess = $paymentIntent->status === 'succeeded';
            } elseif (str_starts_with($transaction->transaction_id, 'sub_')) {
                $stripeSub = $stripe->subscriptions->retrieve($transaction->transaction_id);
                $paymentMethodId = $stripeSub->default_payment_method;
                $isStripeSuccess = in_array($stripeSub->status, ['active', 'trialing']);
            }

            if ($paymentMethodId && $user->stripe_customer_id) {
                try {
                    $stripe->customers->update($user->stripe_customer_id, [
                        'invoice_settings' => ['default_payment_method' => $paymentMethodId]
                    ]);
                } catch (\Exception $e) {
                    // Ignore customer update errors
                }
            }

            $finalStatus = ($request->status == 1 && $isStripeSuccess) ? 'completed' : 'failed';

            $transaction->update([
                'payment_method_id' => $paymentMethodId ?? $transaction->payment_method_id,
                'payment_status' => $finalStatus,
            ]);

            if ($finalStatus === 'completed') {
                if ($transaction->payment_type === 'subscription') {
                    UserSubscription::where('stripe_subscription_id', $transaction->transaction_id)
                        ->orWhere('id', $transaction->transaction_id)
                        ->update(['status' => 'ACTIVE']);
                } else if ($transaction->payment_type === 'one_time') {
                    // One-time plan or voluntary support fallback status updates if previously pending
                    $volSupport = VoluntarySupport::where('stripe_payment_intent_id', $transaction->transaction_id)->first();
                    if ($volSupport) {
                        $volSupport->update(['status' => 'ACTIVE']);
                    }
                }

                $user->update(['last_payment_at' => now()]);
                DB::commit();

                return $this->responseJson(true, 200, 'Payment completed successfully.', []);
            } else {
                DB::commit();

                return $this->responseJson(false, 400, 'Payment failed.', []);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger('Payment completion error: ' . $e->getMessage() . ' -- Line: ' . $e->getLine() . ' -- File: ' . $e->getFile());

            return $this->responseJson(false, 500, 'Something went wrong while processing the payment.', []);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subscription/advanced-search",
     *     operationId="advancedSearch",
     *     tags={"Subscriptions"},
     *     summary="Advanced Discovery Search for Premium Users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="preferred_communication",
     *         in="query",
     *         description="Filter by preferred communication style (comma-separated for multiple)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sleep_rhythm",
     *         in="query",
     *         description="Filter by sleep rhythm (comma-separated for multiple)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="values",
     *         in="query",
     *         description="Filter by personal values/hobbies (comma-separated for multiple)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="kids_future",
     *         in="query",
     *         description="Filter by kids future preference (comma-separated for multiple)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="pets_future",
     *         in="query",
     *         description="Filter by pets future preference (comma-separated for multiple)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="relationship_status",
     *         in="query",
     *         description="Filter by relationship status (comma-separated for multiple)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (default: 10)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to retrieve",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Premium subscription required"
     *     )
     * )
     */
    public function advancedSearch(Request $request)
    {
        $user = auth()->user();

        if (!$user->isPremium()) {
            return $this->responseJson(false, 403, 'Premium subscription required to use advanced filters.', []);
        }

        $query = User::where('id', '!=', $user->id)
            ->where('user_type', 3)
            ->where('is_active', 1)
            ->where('is_blocked', 0);

        // Apply Premium Filters
        $this->applyPremiumFilter($query, $request->input('preferred_communication'), 'preferred_communication');
        $this->applyPremiumFilter($query, $request->input('sleep_rhythm'), 'sleep_rhythm');
        $this->applyHobbyFilter($query, $request->input('values'), 'Values');
        $this->applyPremiumFilter($query, $request->input('kids_future'), 'kids_future');
        $this->applyPremiumFilter($query, $request->input('pets_future'), 'pets_future');
        $this->applyPremiumFilter($query, $request->input('relationship_status'), 'relationship_status');

        $perPage = $request->input('per_page', 10);
        $usersPaginator = $query->with(['profile', 'kycVerification', 'hobbies'])->latest()->paginate($perPage);

        return $this->responseJson(true, 200, 'Advanced search results retrieved successfully', [
            'users' => ProfileResource::collection($usersPaginator->items()),
            'pagination' => [
                'total' => $usersPaginator->total(),
                'count' => $usersPaginator->count(),
                'per_page' => $usersPaginator->perPage(),
                'current_page' => $usersPaginator->currentPage(),
                'total_pages' => $usersPaginator->lastPage(),
            ]
        ]);
    }

    private function applyPremiumFilter($query, $input, $column)
    {
        if (!is_null($input) && $input !== '') {
            $list = is_string($input) ? array_map('trim', explode(',', $input)) : (is_array($input) ? $input : [$input]);
            $list = array_filter($list, fn($v) => $v !== '');
            if (!empty($list)) {
                $query->whereHas('profile', function ($q) use ($column, $list) {
                    $q->whereIn($column, $list);
                });
            }
        }
    }

    private function applyHobbyFilter($query, $input, $hobbyTitle)
    {
        if (!is_null($input) && $input !== '') {
            $list = is_string($input) ? array_map('trim', explode(',', $input)) : (is_array($input) ? $input : [$input]);
            $list = array_filter($list, fn($v) => $v !== '');
            if (!empty($list)) {
                $query->whereHas('hobbies', function ($q) use ($list, $hobbyTitle) {
                    $q->whereIn('name', $list)
                      ->whereHas('hobby', function ($hq) use ($hobbyTitle) {
                          $hq->where('title', $hobbyTitle);
                      });
                });
            }
        }
    }
}
