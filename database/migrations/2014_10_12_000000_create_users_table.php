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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('username')->nullable()->unique()->comment('Users Username');
            $table->tinyInteger('user_type')->default(3)->comment('1:Super Admin,2:All Admin,3:User');
            $table->string('email')->nullable();
            $table->rememberToken();
            $table->string('password')->nullable();
            $table->string('original_password')->nullable();
            $table->bigInteger('phone_code')->default(91)->nullable();
            $table->bigInteger('mobile_number')->nullable();
            $table->string('verification_code')->nullable()->comment('OTP used for verification');
            $table->string('last_login_ip', 100)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->tinyInteger('is_verified_email')->default(false)->comment('0:Not Verified,1:Verified')->nullable();
            $table->tinyInteger('is_verified_phone')->default(false)->comment('0:Not Verified,1:Verified')->nullable();
            $table->tinyInteger('is_active')->default(true)->comment('0:Inactive,1:Active')->nullable();
            $table->tinyInteger('is_approve')->default(true)->comment('0:Unapproved,1:Approved')->nullable();
            $table->tinyInteger('is_blocked')->default(false)->comment('0:Unblocked,1:Blocked')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('device_type')->comment('1:Android,2:IOS,3:Web')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
