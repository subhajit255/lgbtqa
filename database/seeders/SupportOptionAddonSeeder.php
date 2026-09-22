<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\SupportOption;

class SupportOptionAddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            [
                'type' => 'add_on',
                'amount' => 0.99,
                'currency' => 'CHF',
                'label' => 'Post-it Back',
                'description' => '+5 custom Post-its'
            ],
            [
                'type' => 'add_on',
                'amount' => 0.99,
                'currency' => 'CHF',
                'label' => 'Private Browsing Pass',
                'description' => '+30 min private browsing'
            ],
            [
                'type' => 'add_on',
                'amount' => 2.99,
                'currency' => 'CHF',
                'label' => 'Spotlight',
                'description' => '+30 min profile highlight'
            ],
            [
                'type' => 'add_on',
                'amount' => 0.99,
                'currency' => 'CHF',
                'label' => 'Like Pack',
                'description' => '+20 likes instant top-up'
            ]
        ];

        // Delete existing add-ons so we don't duplicate on re-seed
        SupportOption::where('type', 'add_on')->delete();

        foreach ($addons as $addon) {
            SupportOption::create($addon);
        }
    }
}
