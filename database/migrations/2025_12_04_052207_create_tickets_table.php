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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_id')->constrained()->onDelete('cascade');
            $table->foreignId('ceremony_id')->constrained()->onDelete('cascade');
            $table->string('ticket_code')->unique();
            $table->string('qr_code_path')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->enum('ticket_type', ['Base', 'Extra'])->default('Base');
            $table->enum('status', ['Active', 'Used', 'Cancelled', 'Redistributed'])->default('Active');
            $table->boolean('is_scanned')->default(false);
            $table->dateTime('scanned_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['graduate_id', 'status']);
            $table->index(['ceremony_id', 'status']);
            $table->index('ticket_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
