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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Polymorphique : peut logger un Project, Expense, Client, etc.
            $table->nullableMorphs('loggable');

            // Action : 'created_project', 'updated_expense', 'deleted_client', etc.
            $table->string('action');
            $table->string('description')->nullable();

            // Changements after/before (pour historique)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // user ip adresse
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
