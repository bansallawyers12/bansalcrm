<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops unused database tables.
     * 
     * Current tables being dropped:
     * - tasks: Legacy table from removed Task Management System (34 rows)
     * - subject_areas: Subject area management table
     * - states: State/province management table (legacy/unused)
     * - services: Services management table (empty table, infrastructure exists but unused)
     * - quotations: Quotations table (8 rows, feature removed January 2026)
     * - schedule_items: Invoice schedule items table (feature removed January 2026)
     * - education: Education records table (tab removed from UI but functionality still exists)
     * 
     * ⚠️ WARNING: subject_areas table is used in:
     * - Subject Area management pages (CRUD)
     * - Subject management (dropdown selection)
     * - Education records (display and dropdown in clients/partners/products)
     * - Products, Partners, Clients modals
     * 
     * ⚠️ WARNING: services table has infrastructure but is empty:
     * - ServicesController exists with full CRUD
     * - Routes /admin/services/* exist
     * - Views exist (index, create, edit)
     * - Service model exists
     * - Table is empty, so safe to remove, but will break service management pages
     * 
     * ⚠️ WARNING: education table - Tab removed from UI but backend still functional:
     * - EducationController exists with full CRUD (store, edit, geteducations, deleteeducation)
     * - Routes /admin/saveeducation, /admin/editeducation, /admin/get-educations, etc. exist
     * - Education tab content exists in clients/detail.blade.php (hidden but functional)
     * - Used in client merging code (ClientsController lines 4495, 4744)
     * - Remove EducationController, routes, and related code before running migration
     * 
     * Make sure to update/remove related code before running this migration.
     * 
     * To add more tables, add additional Schema::dropIfExists() calls below.
     */
    public function up(): void
    {
        // Drop tasks table (legacy Task Management System - removed January 2026)
        // Tasks are now handled via Note model with task_group field
        Schema::dropIfExists('tasks');
        
        // Drop subject_areas table
        // ⚠️ WARNING: This will break functionality in:
        // - /admin/subjectarea pages (SubjectAreaController)
        // - /admin/subject pages (Subject model uses subject_area field)
        // - Education modals in clients/partners/products
        // - Education display in client detail page
        Schema::dropIfExists('subject_areas');
        
        // Drop states table (legacy/unused - infrastructure exists but no active frontend usage)
        // Route /admin/get_states exists but not used in views/JavaScript
        // State fields in forms use text inputs or hardcoded dropdowns
        Schema::dropIfExists('states');
        
        // Drop services table (empty table - infrastructure exists but table is unused)
        // ⚠️ WARNING: This will break functionality in:
        // - /admin/services pages (ServicesController)
        // - Service management CRUD operations
        // - Service modal functionality
        // Table is empty, so safe to remove, but remove related code first
        Schema::dropIfExists('services');
        
        // Drop quotations table (feature removed January 2026)
        // Only used in client merging code (ClientsController lines 4384-4394, 4692-4702)
        // Quotations feature was removed per CHANGELOG_RECENT_WEEKS.md
        // Update client merging code before running migration
        Schema::dropIfExists('quotations');
        
        // Drop schedule_items table (Invoice Schedule feature removed January 2026)
        // Used in InvoiceController (all methods disabled/commented out)
        // Used in AdminController deleteAction for invoice_schedules (line 934)
        // Invoice Schedule feature was removed per CHANGELOG_RECENT_WEEKS.md
        // Update AdminController deleteAction before running migration
        Schema::dropIfExists('schedule_items');
        
        // Drop education table (Education tab removed from UI January 2026, but backend still functional)
        // ⚠️ WARNING: This will break functionality in:
        // - EducationController (store, edit, geteducations, deleteeducation, getEducationdetail)
        // - Routes: /admin/saveeducation, /admin/editeducation, /admin/get-educations, etc.
        // - Education tab content in clients/detail.blade.php (lines 2254-2309)
        // - Client merging code (ClientsController lines 4495, 4744)
        // - Education modals in clients/partners/products
        // Remove EducationController, routes, and related code before running migration
        Schema::dropIfExists('education');
        
        // Add more table drops here as needed
        // Example:
        // Schema::dropIfExists('table_name');
    }

    /**
     * Reverse the migrations.
     * 
     * WARNING: This migration does not recreate the tables as they are being removed.
     * If rollback is needed, restore from database backup.
     * 
     * Tables being removed:
     * - tasks: 34 rows (legacy Task Management System)
     * - subject_areas: Subject area management table (used in multiple features)
     * - states: State/province management table (legacy/unused)
     * - services: Services management table (empty table, infrastructure exists)
     * - quotations: 8 rows (feature removed January 2026)
     * - schedule_items: Invoice schedule items (feature removed January 2026)
     * - education: Education records table (tab removed from UI but functionality still exists)
     */
    public function down(): void
    {
        // Tables are being removed - do not recreate
        // If rollback is needed, restore from database backup
    }
};
