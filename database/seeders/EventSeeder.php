<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title' => 'Pride Parade & Festival',
                'description' => 'Join us for the annual Pride Parade celebrating love, diversity, and the LGBTQIA+ community. The parade will be followed by a festival with live music, food stalls, and community resources.',
                'about' => 'This is a community-wide event open to all allies and members of the LGBTQIA+ community. Our mission is to promote visibility and celebrate unity.',
                'image' => null,
                'event_date' => date('Y-m-d', strtotime('+15 days')),
                'start_time' => '10:00',
                'end_time' => '18:00',
                'location' => 'Central Park, City Center',
                'host_name' => 'Pride Alliance',
                'host_image' => null,
                'host_type' => 'PARTNER',
                'host_pronouns' => 'They/Them',
                'tags' => 'Pride,Festival,Celebration',
                'audience' => 'Everyone',
                'is_active' => 1,
            ],
            [
                'title' => 'Queer Coffee & Conversation',
                'description' => 'A relaxed, informal meet-up for queer individuals to chat, make friends, and enjoy some coffee. Perfect for newcomers to the city!',
                'about' => 'This gathering is organized to foster local connections in a casual and friendly environment.',
                'image' => null,
                'event_date' => date('Y-m-d', strtotime('+3 days')),
                'start_time' => '09:30',
                'end_time' => '11:30',
                'location' => 'The Rainbow Brew Café',
                'host_name' => 'Alex Rivera',
                'host_image' => null,
                'host_type' => 'COMMUNITY',
                'host_pronouns' => 'She/They',
                'tags' => 'Social,Coffee,Networking',
                'audience' => 'LGBTQIA+',
                'is_active' => 1,
            ],
            [
                'title' => 'Trans & Non-binary Support Group',
                'description' => 'A safe, confidential space for trans, non-binary, genderqueer, and questioning individuals to share experiences and support one another.',
                'about' => 'Facilitated by experienced community counsellors, this circle focus on mental wellbeing, transition journeys, and peer support.',
                'image' => null,
                'event_date' => date('Y-m-d', strtotime('+7 days')),
                'start_time' => '19:00',
                'end_time' => '20:30',
                'location' => 'Community Wellness Center',
                'host_name' => 'Jordan Taylor',
                'host_image' => null,
                'host_type' => 'PARTNER',
                'host_pronouns' => 'He/They',
                'tags' => 'Support,Mental Health,Community',
                'audience' => 'Trans & Non-binary',
                'is_active' => 1,
            ],
            [
                'title' => 'Queer Cinema Night: Movie Screening',
                'description' => 'Join us for a screening of classic and contemporary independent queer films. Popcorn and refreshments will be provided!',
                'about' => 'We will screen two short films followed by a brief discussion about queer representation in modern media.',
                'image' => null,
                'event_date' => date('Y-m-d', strtotime('+10 days')),
                'start_time' => '18:30',
                'end_time' => '21:30',
                'location' => 'Starlight Indie Cinema',
                'host_name' => 'Film Society',
                'host_image' => null,
                'host_type' => 'PARTNER',
                'host_pronouns' => 'They/Them',
                'tags' => 'Movie,Arts,Discussion',
                'audience' => 'Film Lovers',
                'is_active' => 1,
            ],
            [
                'title' => 'LGBTQIA+ Book Club Monthly Meetup',
                'description' => 'Discussing our book of the month, written by a prominent queer author. Bring your thoughts, questions, and a friend!',
                'about' => 'This month we are discussing "The Great Believers" by Rebecca Makkai. All readers are welcome.',
                'image' => null,
                'event_date' => date('Y-m-d', strtotime('+12 days')),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'location' => 'Bookworms Haven, Room B',
                'host_name' => 'Sam Jenkins',
                'host_image' => null,
                'host_type' => 'COMMUNITY',
                'host_pronouns' => 'She/Her',
                'tags' => 'Book Club,Literature,Discussion',
                'audience' => 'Everyone',
                'is_active' => 1,
            ]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
