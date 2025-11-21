<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_sub_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_department_id')->constrained()->onDelete('cascade');
            $table->foreignId('division_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combinations
            $table->unique(['sub_department_id', 'division_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_sub_department');
    }
};