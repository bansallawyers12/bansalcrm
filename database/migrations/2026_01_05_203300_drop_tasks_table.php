<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops unused database tables after cleanup completed.
     * 
     * Current tables being dropped:
     * - tasks: Legacy table from removed Task Management System (34 rows) ✓ Cleanup completed
     * - subject_areas: Subject area management table ✓ Cleanup completed
     * - states: State/province management table (legacy/unused) ✓ Cleanup completed
     * - services: Services management table (empty table) ✓ Cleanup completed
     * - quotations: Quotations table (8 rows, feature removed January 2026) ✓ Cleanup completed
     * - schedule_items: Invoice schedule items table ✓ Cleanup completed
     * - education: Education records table ✓ Cleanup completed
     * 
     * All related files and code have been removed or commented out:
     * ✓ Controllers deleted (SubjectAreaController, ServicesController, EducationController)
     * ✓ Models deleted (SubjectArea, Service, State, Education)
     * ✓ Views deleted (subjectarea/, services/ directories)
     * ✓ Routes commented out (services, subject_area, education, get_states)
     * ✓ Sidebar menu items commented out
     * ✓ View references removed/commented (modals, dropdowns, JavaScript handlers)
     * ✓ Controller references removed (ClientsController merge code, AdminController methods)
     * ✓ Model relationships commented out (Admin::stateData)
     * 
     * This migration is now safe to run.
     */
    public function up(): void
    {
        // Drop tasks table (legacy Task Management System - removed January 2026)
        // Tasks are now handled via Note model with task_group field
        Schema::dropIfExists('tasks');
        
        // Drop subject_areas table
        // All related code removed: SubjectAreaController, SubjectArea model, views, routes, dropdowns
        Schema::dropIfExists('subject_areas');
        
        // Drop states table (legacy/unused)
        // State model deleted, Admin::stateData() relationship commented out, route commented
        Schema::dropIfExists('states');
        
        // Drop services table (empty table)
        // All related code removed: ServicesController, Service model, views, routes, sidebar menu
        Schema::dropIfExists('services');
        
        // Drop quotations table (feature removed January 2026)
        // Client merging code updated to remove quotations handling
        Schema::dropIfExists('quotations');
        
        // Drop schedule_items table (Invoice Schedule feature removed January 2026)
        // No active references found in codebase
        Schema::dropIfExists('schedule_items');
        
        // Drop education table (Education tab removed from UI January 2026)
        // All related code removed: EducationController, Education model, routes, modals, JavaScript handlers
        // Client merging code updated to remove education handling
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
     * Tables removed:
     * - tasks: 34 rows (legacy Task Management System) ✓
     * - subject_areas: Subject area management table ✓
     * - states: State/province management table ✓
     * - services: Services management table (was empty) ✓
     * - quotations: 8 rows (feature removed January 2026) ✓
     * - schedule_items: Invoice schedule items ✓
     * - education: Education records table ✓
     */
    public function down(): void
    {
        // Tables are being removed - do not recreate
        // If rollback is needed, restore from database backup
    }
};
