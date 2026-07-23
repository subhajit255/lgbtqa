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
        if (!Schema::hasColumn('users', 'unique_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('unique_code')->nullable()->unique()->after('uuid');
            });
        }

        // Generate unique codes for existing users
        $users = \Illuminate\Support\Facades\DB::table('users')->whereNull('unique_code')->get();
        foreach ($users as $user) {
            $code = null;
            do {
                $code = strtoupper(\Illuminate\Support\Str::random(10));
            } while (\Illuminate\Support\Facades\DB::table('users')->where('unique_code', $code)->exists());

            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['unique_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'unique_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('unique_code');
            });
        }
    }
};
