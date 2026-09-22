<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status == 'paid') {
                $userId = $session->metadata->user_id ?? $session->client_reference_id;
                $planId = $session->metadata->plan_id ?? null;

                if ($userId && $planId) {
                    UserSubscription::create([
                        'user_id' => $userId,
                        'plan_id' => $planId,
                        'stripe_subscription_id' => $session->subscription ?? $session->payment_intent,
                        'stripe_customer_id' => $session->customer,
                        'status' => 'ACTIVE',
                        'starts_at' => now(),
                        'expires_at' => $session->mode === 'subscription' ? null : now()->addYear(), // Logic can be adjusted based on exact one-time logic
                        'auto_renew' => $session->mode === 'subscription' ? true : false,
                    ]);
                }
            }
        }

        if ($event->type == 'customer.subscription.deleted') {
            $subscription = $event->data->object;
            $userSub = UserSubscription::where('stripe_subscription_id', $subscription->id)->first();
            if ($userSub) {
                $userSub->update([
                    'status' => 'CANCELLED',
                    'expires_at' => now()
                ]);
            }
        }

        return response('Webhook Handled', 200);
    }
}
