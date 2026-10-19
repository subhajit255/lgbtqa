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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'is_all_day')) {
                $table->boolean('is_all_day')->default(false)->after('end_time');
            }
            if (!Schema::hasColumn('events', 'time_zone')) {
                $table->string('time_zone')->nullable()->after('is_all_day');
            }
            if (!Schema::hasColumn('events', 'location_id')) {
                $table->unsignedBigInteger('location_id')->nullable()->after('time_zone');
            }
            if (!Schema::hasColumn('events', 'location_string')) {
                $table->string('location_string')->nullable()->after('location_id');
            }
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('location_string');
            } else {
                $table->string('location')->nullable()->change();
            }
            if (!Schema::hasColumn('events', 'age_restriction')) {
                $table->enum('age_restriction', ['16-17', '18+', 'ALL'])->default('ALL')->after('audience');
            }
            if (!Schema::hasColumn('events', 'official_ticket_url')) {
                $table->string('official_ticket_url')->nullable()->after('age_restriction');
            }
            if (!Schema::hasColumn('events', 'source_attribution')) {
                $table->string('source_attribution')->nullable()->after('official_ticket_url');
            }
            if (!Schema::hasColumn('events', 'admin_status')) {
                $table->enum('admin_status', [
                    'DRAFT', 'SUBMITTED', 'IN_REVIEW', 'CHANGES_REQUESTED', 
                    'APPROVED', 'PUBLISHED', 'PAUSED', 'EXPIRED', 'REVOKED', 'ARCHIVED'
                ])->default('DRAFT')->after('source_attribution');
            }
            if (Schema::hasColumn('events', 'end_time')) {
                $table->string('end_time')->nullable()->change();
            }
            if (Schema::hasColumn('events', 'host_name')) {
                $table->string('host_name')->nullable()->change();
            }
            if (Schema::hasColumn('events', 'start_time')) {
                $table->string('start_time')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'is_all_day',
                'time_zone',
                'location_id',
                'location_string',
                'age_restriction',
                'official_ticket_url',
                'source_attribution',
                'admin_status'
            ] as $col) {
                if (Schema::hasColumn('events', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
