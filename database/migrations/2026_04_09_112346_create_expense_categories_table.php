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
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // ex: "Achat matériel"
            $table->string('color', 7)->default('#6366f1'); // hex color pour UI
            $table->string('icon')->default('receipt_long'); // Material Symbol icon
            $table->boolean('is_system')->default(false); // catégories par défaut non supprimables
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
