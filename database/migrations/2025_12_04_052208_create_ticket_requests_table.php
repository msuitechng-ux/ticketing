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
        Schema::create('ticket_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_id')->constrained()->onDelete('cascade');
            $table->foreignId('ceremony_id')->constrained()->onDelete('cascade');
            $table->integer('requested_quantity');
            $table->integer('approved_quantity')->default(0);
            $table->text('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Partially Approved', 'Denied', 'Waitlisted'])->default('Pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['graduate_id', 'status']);
            $table->index(['ceremony_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_requests');
    }
};
