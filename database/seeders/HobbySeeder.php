<?php

namespace Database\Seeders;

use App\Models\Hobby;
use App\Models\HobbyItem;
use Illuminate\Database\Seeder;

class HobbySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hobbies = [
            'Art' => [
                'Theatre',
                'Painting & Illustration',
                'Photography',
                'Films & Cinema',
                'Music'
            ],
            'Interests' => [
                'Reading',
                'Gaming',
                'Podcasts',
                'Psychology',
                'Tech & innovation'
            ],
            'Identity' => [
                'LGBTQIA+ Pride',
                'Self-Discovery',
                'Gender Expression',
                'Open Conversations',
                'Personal Growth'
            ],
            'Social Impact' => [
                'Sustainability',
                'Mental Health Awareness',
                'Volunteering'
            ],
            'Values' => [
                'Equality',
                'LGBTQIA+ rights',
                'Mental health',
                'Climate / sustainability',
                'Feminism',
                'Disability rights',
                'Anti-racism',
                'Volunteering',
                'Human rights',
                'Community building',
                'Pride'
            ],
            'Alcohol' => [
                'Never',
                'Socially',
                'Sometimes',
                'Often',
                'Sober',
                'Prefer not to say'
            ],
            'Smoking / Nicotine' => [
                'Non-Smoker',
                'Social smoker',
                'Smoker',
                'Vapes / nicotine',
                'Trying to quit',
                'Prefer not to say'
            ],
            'How often do you work out?' => [
                'Daily',
                'Often',
                'Sometimes',
                'Rarely',
                'Never',
                'Prefer not to say'
            ],
            'Diet' => [
                'Omnivore',
                'Vegetarian',
                'Vegan',
                'Pescatarian',
                'Flexitarian',
                'other / self-describe',
                'Prefer not to say'
            ],
            'Sleep rhythm' => [
                'Early bird',
                'Night owl',
                'Depends / both',
                'Prefer not to say'
            ],
            'Do you have kids?' => [
                'No kids',
                'Have kids (with me)',
                'Have kids (not with me)',
                'Prefer not to say'
            ],
            'Kids in the future?' => [
                'Wants kids',
                'Don\'t want kids',
                'Open on it',
                'Not sure',
                'Prefer not to say'
            ],
            'Current pets' => [
                'Dog',
                'Cat',
                'Bird',
                'Fish',
                'Reptile',
                'Small pets',
                'other',
                'No pets'
            ],
            'Pets in future?' => [
                'Want a pet',
                'Don\'t want a pet',
                'Open on it',
                'Allergic',
                'Prefer not to say'
            ],
            'Where do you see yourself living?' => [
                'City',
                'Small town',
                'Countryside',
                'Flexible',
                'Prefer not to say'
            ],
            'Preferred communication' => [
                'Texting',
                'Voice notes',
                'Calls',
                'Video call',
                'In person',
                'Prefer not to say'
            ],
            'Love language' => [
                'Quality time',
                'Words of affirmation',
                'Acts of service',
                'Gift giving',
                'Physical touch'
            ],
            'Social energy' => [
                'Introvert',
                'Extrovert',
                'Ambivert',
                'Prefer not to say'
            ],
            'Personality type (MBTI)' => [
                'ISTJ/ISFJ/INFJ/INTJ',
                'ISTP/ISFP/INFP/INTP',
                'ESTP/ESFP/ENFP/ENTP',
                'ESTJ/ESFJ/ENFJ/ENTJ',
                'Not sure',
                'Prefer not to say'
            ],
            'Education' => [
                'Apprenticeship',
                'High school',
                'Bachelor\'s',
                'Master\'s',
                'PhD',
                'Trade school',
                'Prefer not to say'
            ],
            'How important is travel to you?' => [
                'Not Important',
                'Somewhat important',
                'Important',
                'Very important',
                'Prefer not to say'
            ],
            'Hair length' => [
                'Short',
                'Medium',
                'Long',
                'Shaved / Bald',
                'Other',
                'Prefer not to say'
            ],
            'Tattoos & piercings' => [
                'None',
                'Tattoos',
                'Piercings',
                'Both',
                'Prefer not to say'
            ],
            'Culture & Arts' => [
                'Museum',
                'Galleries',
                'Theater',
                'Opera',
                'Film',
                'Photography',
                'Dance',
                'Poetry',
                'Literature',
                'Comics / Manga',
                'Podcasts',
                'Architecture'
            ],
            'Food & Drink' => [
                'Coffee',
                'Tea',
                'Wine',
                'Beer',
                'Cocktails',
                'Cooking',
                'Baking',
                'Fine Dining',
                'Vegan / Vegetarian food',
                'Street Food'
            ]
        ];

        $typeMap = [
            'Art' => 1,
            'Interests' => 1,
            'Identity' => 1,
            'Social Impact' => 1,
            
            'Alcohol' => 2,
            'Smoking / Nicotine' => 2,
            'How often do you work out?' => 2,
            'Diet' => 2,
            'Sleep rhythm' => 2,
            'Hair length' => 2,
            'Tattoos & piercings' => 2,

            'Do you have kids?' => 3,
            'Kids in the future?' => 3,
            'Current pets' => 3,
            'Pets in future?' => 3,
            'Where do you see yourself living?' => 3,
            'How important is travel to you?' => 3,
            
            'Preferred communication' => 4,
            'Love language' => 4,
            'Social energy' => 4,
            'Personality type (MBTI)' => 4,
            'Education' => 4,

            'Values' => 5,
            'Culture & Arts' => 6,
            'Food & Drink' => 6,
        ];

        foreach ($hobbies as $categoryTitle => $items) {
            $hobby = Hobby::updateOrCreate(
                ['title' => $categoryTitle],
                [
                    'is_active' => 1,
                    'type' => $typeMap[$categoryTitle] ?? 1
                ]
            );

            // Fetch current items to remove any that are not in the new list,
            // or just ensure they are created. Since it's a seed, we can do updateOrCreate for each.
            $existingItemIds = [];
            foreach ($items as $itemName) {
                $item = HobbyItem::updateOrCreate(
                    [
                        'hobby_id' => $hobby->id,
                        'name' => $itemName
                    ],
                    ['is_active' => 1]
                );
                $existingItemIds[] = $item->id;
            }

            // Remove items that are no longer part of this category if we re-run seed
            HobbyItem::where('hobby_id', $hobby->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();
        }
    }
}
