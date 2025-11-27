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
            $table->string('requisition_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); //requested by
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sub_department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('approve_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('clear_status', ['pending', 'cleared'])->default('pending'); //some items can be not in stock so after issuing those or by admin this should be changed to cleard
            $table->foreignId('cleared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cleared_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->enum('status', ['active', 'delete'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};