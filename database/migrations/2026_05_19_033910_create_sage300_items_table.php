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
        Schema::create('sage300_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity_on_hand', 15, 4)->default(0);
            $table->decimal('average_cost', 15, 4)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sage300_items');
    }
};
