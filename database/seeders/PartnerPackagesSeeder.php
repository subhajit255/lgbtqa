<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\PartnerPackage;
use Illuminate\Support\Facades\DB;

class PartnerPackagesSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // Venue & Organiser Packages (Monthly)
            [
                'name' => 'Basic Listing',
                'subtitle' => 'For any venue or organiser.',
                'type' => 'VENUE_ORGANISER',
                'price' => 0,
                'billing_cycle' => 'MONTHLY',
                'features' => [
                    'Appears in discovery lists + map',
                    'Basic info + official link-out'
                ]
            ],
            [
                'name' => 'Partner',
                'subtitle' => 'For regular venues/organisers who want extra visibility.',
                'type' => 'VENUE_ORGANISER',
                'price' => 29,
                'billing_cycle' => 'MONTHLY',
                'features' => [
                    'Optional Partner badge',
                    'Events via feed or standard submission',
                    'Auto-Featured: up to 1 Featured Slot / month'
                ]
            ],
            [
                'name' => 'Spotlight',
                'subtitle' => 'For higher visibility in your city/region',
                'type' => 'VENUE_ORGANISER',
                'price' => 49,
                'billing_cycle' => 'MONTHLY',
                'features' => [
                    'Everything in Partner, plus:',
                    'Auto-Featured: up to 4 Featured Slots / month',
                    'Optional Spotlight badge'
                ]
            ],
            [
                'name' => 'Signature',
                'subtitle' => 'For flagship venues and major organisers.',
                'type' => 'VENUE_ORGANISER',
                'price' => 79,
                'billing_cycle' => 'MONTHLY',
                'features' => [
                    'Everything in Spotlight, plus:',
                    'Auto-Featured: up to 8 Featured Slots / month',
                    'Optional Signature badge'
                ]
            ],
            // Sponsors & Funding Partners (Yearly)
            [
                'name' => 'Community Sponsor',
                'subtitle' => 'Sponsor the launch and core development',
                'type' => 'SPONSOR',
                'price' => 1000,
                'billing_cycle' => 'YEARLY',
                'features' => [
                    'Name/logo on Funding Partners page',
                    'Link-out + thank-you mention'
                ]
            ],
            [
                'name' => 'Launch Sponsor',
                'subtitle' => 'Co-fund safety/moderation initiatives',
                'type' => 'SPONSOR',
                'price' => 5000,
                'billing_cycle' => 'YEARLY',
                'features' => [
                    'Everything in Community Sponsor',
                    'Featured placement in Sponsors section',
                    'Short yearly transparency note'
                ]
            ],
            [
                'name' => 'Impact Sponsor',
                'subtitle' => 'Donate services (legal review, hosting credits, translations)',
                'type' => 'SPONSOR',
                'price' => 15000,
                'billing_cycle' => 'YEARLY',
                'features' => [
                    'Everything in Launch Sponsor',
                    'Clearly described safety/resources initiative',
                    'Optional labelled sponsor slot (limited)'
                ]
            ]
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PartnerPackage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($packages as $pkg) {
            PartnerPackage::create($pkg);
        }
    }
}
