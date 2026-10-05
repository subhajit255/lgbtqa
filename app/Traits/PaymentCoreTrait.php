<?php

namespace App\Traits;

use App\Models\Plan;
use App\Models\SupportOption;
use App\Models\Transaction;
use App\Models\UserSubscription;
use App\Models\VoluntarySupport;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait PaymentCoreTrait
{
    /**
     * Grant a subscription to a user.
     * 
     * @param \App\Models\User $user
     * @param \App\Models\Plan $plan
     * @param string $storeType 'stripe', 'apple', or 'google'
     * @param array $storeData Additional data (e.g., stripe_subscription_id, original_transaction_id)
     * @param string $status
     * @param Carbon|null $startsAt
     * @param Carbon|null $expiresAt
     * @return \App\Models\UserSubscription
     */
    public function grantSubscription($user, $plan, $storeType, $storeData, $status, $startsAt, $expiresAt)
    {
        // Cancel any existing active subscriptions for this user if they are upgrading/switching
        // Note: As per client rules, one paid membership per PM account
        if (in_array($status, ['ACTIVE', 'trialing'])) {
            UserSubscription::where('user_id', $user->id)
                ->where('status', 'ACTIVE')
                ->update(['status' => 'CANCELLED']);
        }

        $subscriptionData = [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'store_type' => $storeType,
            'status' => $status,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'auto_renew' => true,
        ];

        // Merge store specific identifiers
        $subscriptionData = array_merge($subscriptionData, $storeData);

        return UserSubscription::create($subscriptionData);
    }

    /**
     * Grant a voluntary support or consumable add-on to a user.
     *
     * @param \App\Models\User $user
     * @param float $amount
     * @param string $currency
     * @param string $storeType
     * @param array $storeData
     * @param string $status
     * @return \App\Models\VoluntarySupport
     */
    public function grantVoluntarySupport($user, $amount, $currency, $storeType, $storeData, $status)
    {
        $supportData = [
            'user_id' => $user->id,
            'type' => 'ONE_TIME',
            'amount' => $amount,
            'currency' => strtolower($currency),
            'store_type' => $storeType,
            'status' => $status,
        ];

        $supportData = array_merge($supportData, $storeData);

        return VoluntarySupport::create($supportData);
    }

    /**
     * Log a transaction for a purchase.
     *
     * @param \App\Models\User $user
     * @param string|int $transactionId The reference ID (subscription ID, payment intent, etc.)
     * @param string $storeType
     * @param string $paymentType 'subscription', 'one_time', 'free'
     * @param float $amount
     * @param string $currency
     * @param string $paymentStatus 'completed', 'pending', 'failed'
     * @param string $description
     * @param string|null $idempotencyKey
     * @param string|null $paymentMethodId
     * @return \App\Models\Transaction
     */
    public function logTransaction($user, $transactionId, $storeType, $paymentType, $amount, $currency, $paymentStatus, $description, $idempotencyKey = null, $paymentMethodId = null)
    {
        return Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => $transactionId,
            'store_type' => $storeType,
            'payment_type' => $paymentType,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'currency' => strtolower($currency),
            'payment_status' => $paymentStatus,
            'idempotency_key' => $idempotencyKey ?? Str::uuid()->toString(),
            'description' => $description,
        ]);
    }
}
