<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update grn_items table
        Schema::table('grn_items', function (Blueprint $table) {
            // Add location_code if not exists
            if (!Schema::hasColumn('grn_items', 'location_code')) {
                $table->string('location_code')->nullable()->after('unit');
            }
            
            // Add reference numbers from SAGE
            if (!Schema::hasColumn('grn_items', 'reference_number_1')) {
                $table->string('reference_number_1')->nullable()->after('grn_quantity');
            }
            if (!Schema::hasColumn('grn_items', 'reference_number_2')) {
                $table->string('reference_number_2')->nullable()->after('reference_number_1');
            }
            
            // Add processed by and at
            if (!Schema::hasColumn('grn_items', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('grn_items', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
        });
        
        // Update scrap_items table
        Schema::table('scrap_items', function (Blueprint $table) {
            // Add location_code if not exists
            if (!Schema::hasColumn('scrap_items', 'location_code')) {
                $table->string('location_code')->nullable()->after('unit');
            }
            
            // Add processed by and at
            if (!Schema::hasColumn('scrap_items', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('scrap_items', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn([
                'location_code',
                'reference_number_1', 
                'reference_number_2',
                'processed_by',
                'processed_at'
            ]);
        });
        
        Schema::table('scrap_items', function (Blueprint $table) {
            $table->dropColumn([
                'location_code',
                'processed_by',
                'processed_at'
            ]);
        });
    }
};