<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops fee_options and fee_option_types tables.
     * These tables were replaced by application_fee_options and application_fee_option_types
     * which provide application-specific fee customization instead of product-level templates.
     */
    public function up(): void
    {
        // Drop child table first (fee_option_types)
        Schema::dropIfExists('fee_option_types');
        
        // Then drop parent table (fee_options)
        Schema::dropIfExists('fee_options');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This migration does not recreate the tables as they are deprecated.
     * If rollback is needed, restore from backup or recreate manually.
     */
    public function down(): void
    {
        // Tables are deprecated - do not recreate
        // If rollback is needed, restore from backup
    }
};
