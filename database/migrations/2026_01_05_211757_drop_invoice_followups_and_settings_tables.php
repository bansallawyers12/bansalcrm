<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops invoice_followups and settings tables
     * - invoice_followups: Used to track invoice followup activities (email reminders)
     * - settings: Used to store office-specific settings (date_format, time_format)
     */
    public function up(): void
    {
        Schema::dropIfExists('invoice_followups');
        Schema::dropIfExists('settings');
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This migration does not recreate the tables.
     * If rollback is needed, restore from database backup.
     */
    public function down(): void
    {
        // Tables are removed - do not recreate
        // If rollback is needed, restore from database backup
    }
};
