<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Plan;
use Webpatser\Uuid\Uuid;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'PM Free',
                'description' => 'Core community + discovery - always',
                'price' => 0.00,
                'currency' => 'CHF',
                'billing_cycle' => 'MONTHLY',
                'features' => json_encode([
                    'Community spaces + Discovery (People, Events, Places)',
                    'Safety tools built in (Block & Report)',
                    'Privacy - first defaults (You control what you share)',
                    'Matching limits: 20 likes/day',
                    'Posts - list Templates included',
                    'Chat: 5 new conversations /day',
                    'Invite (beta): 2/month'
                ])
            ],
            [
                'name' => 'PM Supporter',
                'description' => 'Voluntary support to keep PM independent ad-free.',
                'price' => 4.90,
                'currency' => 'CHF',
                'billing_cycle' => 'MONTHLY',
                'features' => json_encode([
                    'Support PM\'s development & maintenance',
                    'Supporter badge (optional, hide anytime)',
                    'Matching: 60 likes/day',
                    'Custom Post-its: 5/month',
                    'Private Browsing Pass: 30 min/month',
                    'Spotlight: 1 x 30 min/month',
                    'Chat: 10 new conversations/day',
                    'Invites (beta): 4/month'
                ])
            ],
            [
                'name' => 'PM Plus',
                'description' => 'More discovery tools. Higher limits. More convenience.',
                'price' => 9.90,
                'currency' => 'CHF',
                'billing_cycle' => 'MONTHLY',
                'features' => json_encode([
                    'Everything in Supporter, plus:',
                    'Matching: 150 likes/day',
                    'Custom Post-its: 10/month',
                    'Private Browsing Pass: 60 min/month',
                    'Spotlight: 2 x 30 min/month',
                    'Visitor history tools (opt-in)',
                    'Chat: 20 new conversations/day',
                    'Invites (beta): 8/month'
                ])
            ],
            [
                'name' => 'PM Premium',
                'description' => 'Maximum control + the full premium experience.',
                'price' => 14.90,
                'currency' => 'CHF',
                'billing_cycle' => 'MONTHLY',
                'features' => json_encode([
                    'Everything in Plus, plus:',
                    'Hide "Last online" / appear offline',
                    'Private browsing always on',
                    'Matching: Unlimited (fair use)',
                    'Custom Post-its: Unlimited',
                    'Spotlight: 5 x 30 min/month',
                    'Advanced visibility controls',
                    'Chat: Unlimited new conversations',
                    'Invites (beta): 12/month'
                ])
            ]
        ];

        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Plan::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($plans as $planData) {
            $planData['uuid'] = (string) Uuid::generate(4);
            $planData['is_active'] = 1;
            Plan::create($planData);
        }
    }
}
