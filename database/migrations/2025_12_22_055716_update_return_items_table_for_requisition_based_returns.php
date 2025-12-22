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
        Schema::table('return_items', function (Blueprint $table) {
            // Drop old columns first
            $table->dropColumn(['unit_price', 'total_price', 'return_location_id', 'return_quantity']);
        });
        
        Schema::table('return_items', function (Blueprint $table) {
            // Add new columns with nullable or default values for existing rows
            $table->string('location_code')->nullable()->after('return_type');
            $table->integer('quantity')->default(0)->after('unit');
            $table->unsignedBigInteger('requisition_issued_item_id')->nullable()->after('return_id');
            
            // Add foreign key
            $table->foreign('requisition_issued_item_id')
                  ->references('id')
                  ->on('requisition_issued_items')
                  ->onDelete('cascade');
        });
        
        Schema::table('returns', function (Blueprint $table) {
            // Add requisition reference
            $table->unsignedBigInteger('requisition_id')->nullable()->after('returned_by');
            
            $table->foreign('requisition_id')
                  ->references('id')
                  ->on('requisitions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_items', function (Blueprint $table) {
            $table->dropForeign(['requisition_issued_item_id']);
            $table->dropColumn(['location_code', 'quantity', 'requisition_issued_item_id']);
        });
        
        Schema::table('return_items', function (Blueprint $table) {
            $table->unsignedBigInteger('return_location_id')->nullable()->after('return_type');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->integer('return_quantity')->default(0)->after('unit');
        });
        
        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['requisition_id']);
            $table->dropColumn('requisition_id');
        });
    }
};