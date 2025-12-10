<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->string('return_type', 50); // used, same
            $table->foreignId('return_location_id');
            $table->string('item_code', 255);
            $table->string('item_name', 255);
            $table->string('item_category', 255)->nullable();
            $table->string('unit', 255)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->integer('return_quantity');
            $table->string('approve_status', 50)->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('active'); // active, delete
            
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();
            
            // Foreign keys with NO ACTION to avoid cascade conflicts in MS SQL
            $table->foreign('return_id')->references('id')->on('returns')->onDelete('no action');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};