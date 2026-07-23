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
        Schema::table('profiles', function (Blueprint $table) {
            $table->integer('hair_length')->nullable()->after('hair_color');
            $table->integer('tattoos')->nullable()->after('hair_length');
        });

        // Seed "Hair length"
        $hairLengthCategory = \App\Models\Hobby::updateOrCreate(
            ['title' => 'Hair length'],
            ['is_active' => 1, 'type' => 2]
        );
        $hairLengths = ['Short', 'Medium', 'Long', 'Shaved / Bald', 'Other', 'Prefer not to say'];
        foreach ($hairLengths as $item) {
            \App\Models\HobbyItem::updateOrCreate([
                'hobby_id' => $hairLengthCategory->id,
                'name' => $item
            ], ['is_active' => 1]);
        }

        // Seed "Tattoos & piercings"
        $tattoosCategory = \App\Models\Hobby::updateOrCreate(
            ['title' => 'Tattoos & piercings'],
            ['is_active' => 1, 'type' => 2]
        );
        $tattoosOptions = ['None', 'Tattoos', 'Piercings', 'Both', 'Prefer not to say'];
        foreach ($tattoosOptions as $item) {
            \App\Models\HobbyItem::updateOrCreate([
                'hobby_id' => $tattoosCategory->id,
                'name' => $item
            ], ['is_active' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['hair_length', 'tattoos']);
        });

        $hairLengthCategory = \App\Models\Hobby::where('title', 'Hair length')->first();
        if ($hairLengthCategory) {
            \App\Models\HobbyItem::where('hobby_id', $hairLengthCategory->id)->delete();
            $hairLengthCategory->delete();
        }

        $tattoosCategory = \App\Models\Hobby::where('title', 'Tattoos & piercings')->first();
        if ($tattoosCategory) {
            \App\Models\HobbyItem::where('hobby_id', $tattoosCategory->id)->delete();
            $tattoosCategory->delete();
        }
    }
};
