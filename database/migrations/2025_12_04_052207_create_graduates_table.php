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
        Schema::create('graduates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ceremony_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique();
            $table->string('student_name');
            $table->enum('degree_level', ['Undergraduate', 'Masters', 'PhD']);
            $table->string('faculty');
            $table->string('department');
            $table->integer('tickets_allocated')->default(2);
            $table->integer('extra_tickets_requested')->default(0);
            $table->integer('extra_tickets_approved')->default(0);
            $table->integer('tickets_used')->default(0);
            $table->timestamps();

            $table->index(['ceremony_id', 'faculty']);
            $table->index(['ceremony_id', 'degree_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduates');
    }
};
