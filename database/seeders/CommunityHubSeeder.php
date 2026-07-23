<?php

namespace Database\Seeders;

use App\Models\CommunityHub;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityHubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hubs = [
            'Events',
            'News',
            'Voting',
            'Shop',
            'Artists',
            'Counselling',
            'Speed Dating',
            'Premium',
            'Support',
            'Sponsoring',
            'deafult'
        ];

        foreach ($hubs as $hub) {
            CommunityHub::create([
                'title' => $hub,
                'slug' => Str::slug($hub),
                'is_active' => true,
            ]);
        }
    }
}
