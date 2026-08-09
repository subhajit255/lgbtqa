<?php

namespace App\Traits;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

trait FeedRecommendationsTrait
{
    private function getRecommendedCommunities()
    {
        return Community::where('is_active', 1)
            ->withCount(['members' => function ($q) {
                $q->where('status', 'active');
            }])
            ->with('creator')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($c) {
                $category = 'LGBTQ+ Community';
                if (stripos($c->name, 'Lesbian') !== false || stripos($c->description, 'Lesbian') !== false) {
                    $category = 'Lesbian Community';
                }

                return [
                    'id' => $c->id,
                    'uuid' => $c->uuid,
                    'name' => $c->name,
                    'image_path' => $c->image_path,
                    'type' => $c->type,
                    'tags' => $c->tags,
                    'members_count' => $c->members_count,
                    'category' => $category,
                    'creator_username' => $c->creator->name ?? 'system',
                    'time_created_diff' => $c->created_at ? $c->created_at->diffForHumans() : 'now',
                ];
            });
    }

    private function getRecommendedEvents()
    {
        return Event::where('is_active', 1)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($e) {
                $dateFormatted = $e->event_date;
                try {
                    $dateFormatted = Carbon::parse($e->event_date)->format('M d • D');
                } catch (\Throwable $err) {
                }

                $attendees = $e->joinedUsers()
                    ->take(3)
                    ->get()
                    ->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'image_path' => $u->image_path,
                        ];
                    });

                return [
                    'id' => $e->id,
                    'uuid' => $e->uuid,
                    'title' => $e->title,
                    'description' => $e->description,
                    'image_path' => $e->image_path,
                    'event_date_formatted' => $dateFormatted,
                    'location' => $e->location,
                    'going_count' => $e->joinedUsers()->count(),
                    'time_range' => "{$e->start_time} – {$e->end_time}",
                    'attendees' => $attendees,
                ];
            });
    }

    private function getNearbyUsers()
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return [];
        }

        return User::where('id', '!=', $currentUser->id)
            ->whereHas('profile')
            ->with('profile')
            ->take(5)
            ->get()
            ->map(function ($u) {
                // Mock slightly offset coordinates close to Zurich (Switzerland base)
                $latBase = 47.3769;
                $lngBase = 8.5417;
                $randOffsetLat = rand(-200, 200) / 10000;
                $randOffsetLng = rand(-200, 200) / 10000;

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'image_path' => $u->image_path,
                    'city' => $u->profile->living_in_city ?? 'Zurich',
                    'country' => $u->profile->living_in_country ?? 'Switzerland',
                    'latitude' => $latBase + $randOffsetLat,
                    'longitude' => $lngBase + $randOffsetLng,
                ];
            });
    }

    private function getMatches()
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return [];
        }

        $currentUserHobbies = $currentUser->hobbies->pluck('id')->toArray();

        return User::where('id', '!=', $currentUser->id)
            ->whereHas('profile')
            ->with(['profile', 'hobbies'])
            ->take(5)
            ->get()
            ->map(function ($u) use ($currentUserHobbies) {
                $otherUserHobbies = $u->hobbies->pluck('id')->toArray();
                $overlap = count(array_intersect($currentUserHobbies, $otherUserHobbies));

                // Match score algorithm: base 70% + 5% per common hobby, capped at 98%
                $score = min(98, 70 + ($overlap * 5));
                if ($score == 70) {
                    $score = rand(75, 95); // default fallback
                }

                $dob = $u->profile->dob;
                $age = $u->profile->age;
                if (!$age && $dob) {
                    try {
                        $age = Carbon::parse($dob)->age;
                    } catch (\Throwable $err) {
                    }
                }

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'age' => $age ?? rand(20, 30),
                    'image_path' => $u->image_path,
                    'city' => $u->profile->living_in_city ?? 'Zurich',
                    'country' => $u->profile->living_in_country ?? 'Switzerland',
                    'match_percentage' => "{$score}% match",
                ];
            });
    }
}
