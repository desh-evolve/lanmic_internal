<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->onDelete('cascade');
            $table->string('item_code', 255);
            $table->string('item_name', 255);
            $table->string('item_category', 255)->nullable();
            $table->string('unit', 255)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->integer('quantity');
            $table->string('status', 50)->default('pending'); // pending, cleared
            $table->foreignId('cleared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cleared_at')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};