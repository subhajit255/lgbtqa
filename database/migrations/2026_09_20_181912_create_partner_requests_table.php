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
        Schema::create('partner_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('organization_name');
            $table->string('email');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['PENDING', 'REVIEWED', 'CONTACTED'])->default('PENDING');
            $table->timestamps();
            
            $table->foreign('package_id')->references('id')->on('partner_packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_requests');
    }
};
