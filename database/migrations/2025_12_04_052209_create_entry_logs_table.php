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
        Schema::create('entry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('ceremony_id')->constrained()->onDelete('cascade');
            $table->foreignId('scanned_by')->constrained('users');
            $table->dateTime('scanned_at');
            $table->string('entry_point')->nullable();
            $table->enum('verification_status', ['Success', 'Duplicate', 'Invalid', 'Fraud Attempt'])->default('Success');
            $table->text('notes')->nullable();
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->index(['ceremony_id', 'scanned_at']);
            $table->index(['ticket_id', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_logs');
    }
};
