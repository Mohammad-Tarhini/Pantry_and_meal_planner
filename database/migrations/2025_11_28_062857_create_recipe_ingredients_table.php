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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
        $table->id();
        $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
        $table->string('item_name');
        $table->decimal('quantity', 10, 2);
        $table->foreignId('unit_id')->constrained('units');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
