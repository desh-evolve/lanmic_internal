<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_number', 255)->unique();
            $table->unsignedBigInteger('user_id'); //requested by
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sub_department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('set null');
            $table->string('approve_status', 50)->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('clear_status', 50)->default('pending'); // pending, cleared - some items can be not in stock so after issuing those or by admin this should be changed to cleared
            $table->unsignedBigInteger('cleared_by')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->string('status', 50)->default('active'); // active, delete
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();
            
            // Foreign keys with NO ACTION to avoid cascade conflicts in MS SQL
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('cleared_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};