<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisition_issued_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requisition_id');
            $table->unsignedBigInteger('requisition_item_id');
            $table->string('item_code', 255);
            $table->string('item_name', 255);
            $table->string('item_category', 255)->nullable();
            $table->string('unit', 255)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->integer('issued_quantity');
            $table->unsignedBigInteger('issued_by');
            $table->timestamp('issued_at');
            $table->string('status', 50)->default('active'); // active, delete
            
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();
            
            // Foreign keys with NO ACTION to avoid cascade conflicts in MS SQL
            $table->foreign('requisition_id')->references('id')->on('requisitions')->onDelete('no action');
            $table->foreign('requisition_item_id')->references('id')->on('requisition_items')->onDelete('no action');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_issued_items');
    }
};