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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('display_name')->nullable();
            $table->text('about')->nullable();
            $table->date('dob')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->tinyInteger('gender')->comment('1:Male,2:Female,3:Trans Man,4:Trans Woman,5:Non-binary,6:Agender,7:Genderfluid,8:Genderqueer,9:Other')->nullable();
            $table->tinyInteger('orientation')->comment('1:Straight,2:Gay,3:Bisexual,4:Lesbian,5:Pansexual,6:Queer,7:Asexual,8:Demisexual,9:Other')->nullable();
            $table->tinyInteger('dating_preferences')->comment('0:All,1:Male,2:Female,3:Trans Man,4:Trans Woman,5:Non-binary,6:Agender,7:Genderfluid,8:Genderqueer,9:Other')->nullable();

            $table->tinyInteger('age_range')->default(1)->comment('Age Range')->nullable();
            $table->tinyInteger('distance_range')->default(1)->comment('Distance Range')->nullable();

            $table->boolean('friends')->default(true)->comment('Looking For')->nullable();
            $table->boolean('dates')->default(true)->comment('Looking For')->nullable();
            $table->boolean('events_and_communities')->default(true)->comment('Looking For')->nullable();
            $table->boolean('hookups')->default(true)->comment('Looking For')->nullable();
            $table->boolean('guest_mode')->default(false)->comment('Safety & Privacy')->nullable();
            $table->boolean('verified_profiles')->default(false)->comment('Safety & Privacy')->nullable();
            $table->boolean('invite_only_access')->default(false)->comment('Safety & Privacy')->nullable();
            $table->boolean('no_tracking')->default(false)->comment('Safety & Privacy')->nullable();
            $table->boolean('everyone')->default(true)->comment('Visibility Control')->nullable();
            $table->boolean('selected_groups')->default(false)->comment('Visibility Control')->nullable();
            $table->boolean('no_one_at_all')->default(false)->comment('Visibility Control')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
