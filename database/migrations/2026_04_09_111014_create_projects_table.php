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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('reference')->nullable()->unique();
            $table->text('description')->nullable();

            // Relation
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_lead_id')->nullable()->constrained('users')->nullOnDelete();

            // global Budget 
            $table->decimal('budget', 15, 2)->default(0);

            // Budget breakdown (optional, total = budget)
            $table->decimal('budget_main_oeuvre', 15, 2)->default(0);
            $table->decimal('budget_materiel', 15, 2)->default(0);
            $table->decimal('budget_transport', 15, 2)->default(0);
            $table->decimal('budget_autres', 15, 2)->default(0);

            // Date
            $table->date('start_date');
            $table->date('end_date_planned');
            $table->date('end_date_actual')->nullable();
            $table->enum('status', ['en_cours', 'termine', 'en_pause'])->default('en_cours');

            // Suppliers / contacts 
            $table->json('suppliers')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
