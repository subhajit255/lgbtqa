<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Values category type to 5
        \App\Models\Hobby::where('title', 'Values')->update(['type' => 5]);

        // 2. Seed Culture & Arts (Type 6)
        $cultureArtsCategory = \App\Models\Hobby::updateOrCreate(
            ['title' => 'Culture & Arts'],
            ['is_active' => 1, 'type' => 6]
        );
        $cultureArtsItems = [
            'Museum', 'Galleries', 'Theater', 'Opera', 'Film', 'Photography',
            'Dance', 'Poetry', 'Literature', 'Comics / Manga', 'Podcasts', 'Architecture'
        ];
        foreach ($cultureArtsItems as $item) {
            \App\Models\HobbyItem::updateOrCreate([
                'hobby_id' => $cultureArtsCategory->id,
                'name' => $item
            ], ['is_active' => 1]);
        }

        // 3. Seed Food & Drink (Type 6)
        $foodDrinkCategory = \App\Models\Hobby::updateOrCreate(
            ['title' => 'Food & Drink'],
            ['is_active' => 1, 'type' => 6]
        );
        $foodDrinkItems = [
            'Coffee', 'Tea', 'Wine', 'Beer', 'Cocktails', 'Cooking', 'Baking',
            'Fine Dining', 'Vegan / Vegetarian food', 'Street Food'
        ];
        foreach ($foodDrinkItems as $item) {
            \App\Models\HobbyItem::updateOrCreate([
                'hobby_id' => $foodDrinkCategory->id,
                'name' => $item
            ], ['is_active' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert Values category type to 1
        \App\Models\Hobby::where('title', 'Values')->update(['type' => 1]);

        // 2. Delete Culture & Arts
        $cultureArtsCategory = \App\Models\Hobby::where('title', 'Culture & Arts')->first();
        if ($cultureArtsCategory) {
            \App\Models\HobbyItem::where('hobby_id', $cultureArtsCategory->id)->delete();
            $cultureArtsCategory->delete();
        }

        // 3. Delete Food & Drink
        $foodDrinkCategory = \App\Models\Hobby::where('title', 'Food & Drink')->first();
        if ($foodDrinkCategory) {
            \App\Models\HobbyItem::where('hobby_id', $foodDrinkCategory->id)->delete();
            $foodDrinkCategory->delete();
        }
    }
};
