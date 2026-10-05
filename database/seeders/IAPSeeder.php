<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\SupportOption;

class IAPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Memberships (Plans)
        $plans = [
            ['name' => 'PM Plus', 'price' => 9.90, 'currency' => 'chf', 'billing_cycle' => 'MONTHLY', 'is_active' => 1],
            ['name' => 'PM Premium', 'price' => 14.90, 'currency' => 'chf', 'billing_cycle' => 'MONTHLY', 'is_active' => 1],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['name' => $planData['name']],
                $planData
            );
        }

        // 2. PM Supporter (Monthly)
        $supporterAmounts = [4.90, 10, 20, 30, 50, 100, 200, 500];
        foreach ($supporterAmounts as $amount) {
            SupportOption::updateOrCreate(
                ['type' => 'pm_supporter_' . $amount, 'amount' => $amount],
                ['label' => 'PM Supporter ' . $amount, 'currency' => 'chf']
            );
        }

        // 3. One-Time Add-ons (Consumables)
        $addOns = [
            // Post-its
            ['type' => 'post_it_5', 'label' => 'Post-it (5)', 'amount' => 1.90],
            ['type' => 'post_it_15', 'label' => 'Post-it (15)', 'amount' => 4.90],
            ['type' => 'post_it_30', 'label' => 'Post-it (30)', 'amount' => 7.90],

            // Private Browsing Pass
            ['type' => 'private_browsing_30', 'label' => 'Private Browsing Pass (30 min)', 'amount' => 1.90],
            ['type' => 'private_browsing_90', 'label' => 'Private Browsing Pass (90 min)', 'amount' => 4.90],
            ['type' => 'private_browsing_180', 'label' => 'Private Browsing Pass (180 min)', 'amount' => 7.90],

            // Spotlight
            ['type' => 'spotlight_1', 'label' => 'Spotlight (1 x 30 min)', 'amount' => 2.90],
            ['type' => 'spotlight_3', 'label' => 'Spotlight (3 x 30 min)', 'amount' => 6.90],
            ['type' => 'spotlight_5', 'label' => 'Spotlight (5 x 30 min)', 'amount' => 9.90],

            // Like Pack
            ['type' => 'like_pack_20', 'label' => 'Like Pack (20)', 'amount' => 1.90],
            ['type' => 'like_pack_60', 'label' => 'Like Pack (60)', 'amount' => 4.90],
            ['type' => 'like_pack_120', 'label' => 'Like Pack (120)', 'amount' => 7.90],
        ];

        foreach ($addOns as $addOn) {
            SupportOption::updateOrCreate(
                ['type' => $addOn['type']],
                ['label' => $addOn['label'], 'amount' => $addOn['amount'], 'currency' => 'chf']
            );
        }

        // 4. One-time Support
        $oneTimeSupportAmounts = [2, 5, 10, 20, 30, 50, 100, 150, 200, 300, 400, 500, 750];
        foreach ($oneTimeSupportAmounts as $amount) {
            SupportOption::updateOrCreate(
                ['type' => 'one_time_support_' . $amount, 'amount' => $amount],
                ['label' => 'One-time Support ' . $amount, 'currency' => 'chf']
            );
        }
    }
}
