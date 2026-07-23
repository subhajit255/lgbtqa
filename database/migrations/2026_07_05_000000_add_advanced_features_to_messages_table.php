<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_edited')->default(false)->after('attachment');
            $table->boolean('is_pinned')->default(false)->after('is_edited');
            $table->foreignId('reply_to_message_id')->nullable()->constrained('messages')->nullOnDelete()->after('is_pinned');
            $table->foreignId('forwarded_from_message_id')->nullable()->constrained('messages')->nullOnDelete()->after('reply_to_message_id');
            $table->boolean('is_forwarded')->default(false)->after('forwarded_from_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['reply_to_message_id']);
                $table->dropForeign(['forwarded_from_message_id']);
            }
            $table->dropColumn([
                'is_edited',
                'is_pinned',
                'reply_to_message_id',
                'forwarded_from_message_id',
                'is_forwarded'
            ]);
        });
    }
};
