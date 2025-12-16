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
        Schema::table('requisition_issued_items', function (Blueprint $table) {
            $table->string('reference_number_1')->nullable()->after('location_code');
            $table->string('reference_number_2')->nullable()->after('reference_number_1');
            $table->text('notes')->nullable()->after('reference_number_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisition_issued_items', function (Blueprint $table) {
            $table->dropColumn(['reference_number_1', 'reference_number_2', 'notes']);
        });
    }
};