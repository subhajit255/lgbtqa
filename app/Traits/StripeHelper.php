<?php

namespace App\Traits;

use Stripe\Customer;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Subscription;

trait StripeHelper
{
    /**
     * Set up Stripe API Key
     */
    protected function initStripe()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Sync a plan's product and price with Stripe.
     * 
     * @param \App\Models\Plan|null $plan
     * @param array $data
     * @return array
     */
    public function syncStripePlan($plan, $data)
    {
        $this->initStripe();

        $stripeProductId = $plan ? $plan->stripe_product_id : null;
        $stripePriceId = $plan ? $plan->stripe_price_id : null;

        // 1. Manage Product
        if (!$stripeProductId) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
            ]);
            $stripeProductId = $product->id;
        } else {
            Product::update($stripeProductId, [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
            ]);
        }

        // 2. Manage Price
        $needsNewPrice = false;
        if (!$stripePriceId) {
            $needsNewPrice = true;
        } else if ($plan) {
            if ($plan->price != $data['price'] || $plan->currency != $data['currency'] || $plan->billing_cycle != $data['billing_cycle']) {
                $needsNewPrice = true;
                try {
                    Price::update($stripePriceId, ['active' => false]);
                } catch (\Exception $e) {
                    // ignore if price already inactive or doesn't exist
                }
            }
        }

        if ($needsNewPrice) {
            $priceData = [
                'product' => $stripeProductId,
                'unit_amount' => (int)($data['price'] * 100),
                'currency' => strtolower($data['currency']),
            ];

            if ($data['billing_cycle'] === 'MONTHLY') {
                $priceData['recurring'] = ['interval' => 'month'];
            } elseif ($data['billing_cycle'] === 'YEARLY') {
                $priceData['recurring'] = ['interval' => 'year'];
            }

            $priceObj = Price::create($priceData);
            $stripePriceId = $priceObj->id;
        }

        return [
            'stripe_product_id' => $stripeProductId,
            'stripe_price_id' => $stripePriceId,
        ];
    }
    public function createCustomer(string $email, ?string $name = null, array $params = [])
    {
        $this->initStripe();

        $payload = array_merge(array_filter([
            'email' => $email,
            'name' => $name,
        ]), $params);

        $customer = Customer::create($payload);

        return $customer;
    }
    /**
     * Create a recurring Stripe subscription.
     *
     * @param string $customerId The Stripe Customer ID (cus_...)
     * @param string $priceId The Stripe Price ID (price_...)
     * @param array $params Additional parameters to pass to Stripe
     * @return \Stripe\Subscription
     */
    public function createSubscription(string $customerId, string $priceId, array $params = [])
    {
        $this->initStripe();

        $payload = array_merge([
            'customer' => $customerId,
            'items' => [
                ['price' => $priceId],
            ],
            // Expand the latest invoice and payment intent to handle initial 3DS auth if required
            'expand' => ['latest_invoice.payment_intent'],
        ], $params);

        $subscription = Subscription::create($payload);

        return $subscription;
    }
}
