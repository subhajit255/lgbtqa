<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::first();

        if (!$creator) {
            // Fallback user if database is completely empty
            $creator = User::factory()->create([
                'name' => 'Default Creator',
                'username' => 'defaultcreator',
                'email' => 'creator@example.com',
            ]);
        }

        $communities = [
            [
                'name' => 'Lesbian & Queer Women',
                'description' => 'A supportive and vibrant space for lesbians, bisexual, pansexual, and queer women to discuss, support each other, and network.',
                'image' => null,
                'creator_id' => $creator->id,
                'type' => 'public',
                'tags' => 'Lesbian,Queer,Women,Support',
                'is_active' => 1,
            ],
            [
                'name' => 'Transgender & Non-Binary Support',
                'description' => 'A dedicated safe space for trans, non-binary, genderqueer, agender, and questioning individuals to share their stories, advice, and transition support.',
                'image' => null,
                'creator_id' => $creator->id,
                'type' => 'public',
                'tags' => 'Trans,Non-Binary,Support,Confidential',
                'is_active' => 1,
            ],
            [
                'name' => 'Queer Gamers Guild',
                'description' => 'A community for LGBTQIA+ geeks, gamers, and nerd-culture enthusiasts. Let\'s play multiplayer games, chat about gaming news, and host tournaments!',
                'image' => null,
                'creator_id' => $creator->id,
                'type' => 'public',
                'tags' => 'Gaming,Geek,Social,Fun',
                'is_active' => 1,
            ],
            [
                'name' => 'Gay & Bi Men Networking',
                'description' => 'Connecting gay, bisexual, pansexual, and queer men for professional networking, mentoring, social meetups, and community discussions.',
                'image' => null,
                'creator_id' => $creator->id,
                'type' => 'public',
                'tags' => 'Gay,Bi,Men,Networking',
                'is_active' => 1,
            ],
            [
                'name' => 'LGBTQIA+ Parents & Families',
                'description' => 'A group for LGBTQIA+ parents, guardians, and prospective parents to talk about parenting, family dynamics, and child care in a friendly and inclusive environment.',
                'image' => null,
                'creator_id' => $creator->id,
                'type' => 'private',
                'tags' => 'Parenting,Family,Support,Private',
                'is_active' => 1,
            ]
        ];

        foreach ($communities as $community) {
            Community::create($community);
        }
    }
}
