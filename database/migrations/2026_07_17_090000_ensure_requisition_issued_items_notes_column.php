<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defensive fix for environments where 2025_12_11_044120_add_ref_numbers_to_
 * requisition_issued_items_table (which added location_code, reference_number_1/2,
 * and notes) was never applied — e.g. because the migration file was deployed
 * without ever running `php artisan migrate`. Every check is wrapped in
 * hasColumn() so this is safe to run even if some/all columns already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisition_issued_items', function (Blueprint $table) {
            if (!Schema::hasColumn('requisition_issued_items', 'location_code')) {
                $table->string('location_code', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('requisition_issued_items', 'reference_number_1')) {
                $table->string('reference_number_1')->nullable();
            }
            if (!Schema::hasColumn('requisition_issued_items', 'reference_number_2')) {
                $table->string('reference_number_2')->nullable();
            }
            if (!Schema::hasColumn('requisition_issued_items', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally a no-op: this migration only backfills columns that
        // should already exist per the original schema. Dropping them here
        // would be destructive if they were already in use before this ran.
    }
};
