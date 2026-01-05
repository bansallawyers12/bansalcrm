<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops the following tables: cities, enquiries, checkin_histories, taxes
     * 
     * ⚠️ WARNING: These tables are actually USED in the codebase:
     * - cities: Used by City model in AdminController.php
     * - enquiries: Used by Enquiry model in EnquireController.php
     * - checkin_histories: Used by CheckinHistory model in OfficeVisitController.php
     * - taxes: Used by Tax model in TaxController.php and invoice views
     * 
     * Removing these tables will break functionality. Make sure you have updated
     * the code that references these tables before running this migration.
     */
    public function up(): void
    {
        // Drop tables
        Schema::dropIfExists('cities');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('checkin_histories');
        Schema::dropIfExists('taxes');
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This migration does not recreate the tables as they are being removed.
     * If rollback is needed, restore from database backup.
     * 
     * These tables contained the following data (approximate):
     * - cities: 48 rows
     * - enquiries: 1,475 rows
     * - checkin_histories: 95,527 rows
     * - taxes: 1 row (may have been removed in previous migration)
     */
    public function down(): void
    {
        // Tables are being removed - do not recreate
        // If rollback is needed, restore from database backup
    }
};

