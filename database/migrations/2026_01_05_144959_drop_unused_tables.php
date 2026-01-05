<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops unused database tables identified in UNUSED_TABLES_REPORT.md (serial 1-13)
     * Tables being removed: api_tokens, check_partners, currencies, invoice_schedules,
     * postcode_ranges, quotation_infos, service_fee_option_types, service_fee_options,
     * suburbs, task_logs, tax_rates, taxes, to_do_groups
     * 
     * NOTE: The 'verify_users' table (serial #14) is excluded per user request.
     * 
     * ⚠️ WARNING: The 'taxes' table is included and will be removed. This table is used
     * by TaxController.php and invoice views, so removing it may break functionality.
     */
    public function up(): void
    {
        // Drop tables in order (child tables first if foreign keys exist)
        
        // Service fee option tables (child first)
        Schema::dropIfExists('service_fee_option_types');
        Schema::dropIfExists('service_fee_options');
        
        // Quotation info table (related to removed quotations feature)
        Schema::dropIfExists('quotation_infos');
        
        // Invoice scheduling table
        Schema::dropIfExists('invoice_schedules');
        
        // Task logs table
        Schema::dropIfExists('task_logs');
        
        // Tax rates table
        Schema::dropIfExists('tax_rates');
        
        // Taxes table (removed per user request)
        Schema::dropIfExists('taxes');
        
        // To-do groups table
        Schema::dropIfExists('to_do_groups');
        
        // Geographic/location tables
        Schema::dropIfExists('suburbs');
        Schema::dropIfExists('postcode_ranges');
        Schema::dropIfExists('currencies');
        
        // Check partners table
        Schema::dropIfExists('check_partners');
        
        // API tokens table
        Schema::dropIfExists('api_tokens');
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This migration does not recreate the tables as they are unused/deprecated.
     * If rollback is needed, restore from database backup.
     * 
     * These tables contained the following data:
     * - api_tokens: 3 rows
     * - check_partners: 3 rows
     * - currencies: 6 rows
     * - invoice_schedules: 6 rows
     * - postcode_ranges: 480 rows
     * - quotation_infos: 13 rows
     * - service_fee_option_types: 1 row
     * - service_fee_options: 1 row
     * - suburbs: 18,530 rows
     * - task_logs: 117 rows
     * - tax_rates: 7 rows
     * - taxes: 1 row (⚠️ WARNING: This table is actually used!)
     * - to_do_groups: 5 rows
     */
    public function down(): void
    {
        // Tables are unused/deprecated - do not recreate
        // If rollback is needed, restore from database backup
    }
};

