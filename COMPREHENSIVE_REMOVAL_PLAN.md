# Comprehensive Removal Plan - Bansal CRM2

**Created:** January 3, 2026  
**Status:** ⏳ Planning Phase - Not Yet Implemented  
**Based On:** CHANGELOG_RECENT_WEEKS.md analysis

---

## Table of Contents

1. [Overview](#overview)
2. [Removal Categories](#removal-categories)
3. [Detailed Removal Plan](#detailed-removal-plan)
4. [Files to Delete](#files-to-delete)
5. [Routes to Remove](#routes-to-remove)
6. [Controller Methods to Remove](#controller-methods-to-remove)
7. [Model References to Clean](#model-references-to-clean)
8. [View Files to Remove](#view-files-to-remove)
9. [JavaScript/CSS Cleanup](#javascriptcss-cleanup)
10. [Database Considerations](#database-considerations)
11. [Implementation Order](#implementation-order)
12. [Verification Checklist](#verification-checklist)

---

## Overview

This document outlines a comprehensive plan to remove all obsolete functions, files, and references mentioned in the CHANGELOG_RECENT_WEEKS.md. The plan is organized by feature/system to ensure systematic removal while maintaining code integrity.

**Key Principles:**
- Remove dead code and unused files
- Clean up routes that reference removed features
- Remove model files for deleted features
- Clean up view files that are no longer used
- Remove JavaScript handlers for removed features
- Preserve database tables (as per changelog policy)
- Maintain backward compatibility where needed

---

## Removal Categories

### Category 1: Completely Removed Features (Files Still Exist)
- Task Management System
- Tax Management System
- Enquiry Source System
- Promo Code System
- Invoice Schedule System
- Quotations System

### Category 2: Partially Removed Features (Cleanup Needed)
- Email Option from Partners (individual email)
- Remaining references to removed features

### Category 3: Obsolete Code References
- Commented-out code blocks
- Dead JavaScript handlers
- Unused CSS classes

---

## Detailed Removal Plan

### 1. Task Management System Removal

**Status:** UI removed, but files still exist

#### Files to Delete:
- `app/Http/Controllers/Admin/TasksController.php` ✅ (Still exists - needs deletion)
- `app/Models/Task.php` ✅ (Still exists - needs deletion)
- `app/Models/TaskLog.php` ✅ (Still exists - needs deletion)
- `app/Models/ToDoGroup.php` ✅ (Still exists - needs deletion, used by task system)
- `app/Console/Commands/CompleteTaskRemoval.php` (if exists)
- `app/Console/Commands/InPersonCompleteTaskRemoval.php` (if exists)

#### View Files to Delete:
- `resources/views/Admin/tasks/index.blade.php` ✅ (Still exists)
- All task-related view files in `resources/views/Admin/tasks/`

#### Routes to Remove from `routes/web.php`:
```php
// Lines 417-432 (approximately)
Route::get('/tasks', 'Admin\TasksController@index')->name('admin.tasks.index');
Route::get('/tasks/archive/{id}', 'Admin\TasksController@taskArchive')->name('admin.tasks.archive');
Route::get('/tasks/create', 'Admin\TasksController@create')->name('admin.tasks.create');
Route::post('/tasks/store', 'Admin\TasksController@store')->name('admin.tasks.store');
Route::post('/tasks/groupstore', 'Admin\TasksController@groupstore')->name('admin.tasks.groupstore');
Route::post('/tasks/deletegroup', 'Admin\TasksController@deletegroup')->name('admin.tasks.deletegroup');
Route::get('/get-tasks', 'Admin\TasksController@gettasks')->name('admin.tasks.gettasks');
Route::get('/get-task-detail', 'Admin\TasksController@taskdetail')->name('admin.tasks.gettaskdetail');
Route::post('/update_task_comment', 'Admin\TasksController@update_task_comment');
Route::post('/update_task_description', 'Admin\TasksController@update_task_description');
Route::post('/update_task_status', 'Admin\TasksController@update_task_status');
Route::post('/update_task_priority', 'Admin\TasksController@update_task_priority');
Route::post('/updateduedate', 'Admin\TasksController@updateduedate');
Route::get('/task/change_assignee', 'Admin\TasksController@change_assignee');

// Task Report Routes (Lines ~712-713)
Route::get('/report/task/personal-task-report', 'Admin\ReportController@personal_task')->name('admin.reports.personal-task-report');
Route::get('/report/task/office-task-report', 'Admin\ReportController@office_task')->name('admin.reports.office-task-report');

// Assignee Task Routes (Lines ~759-760)
Route::post('/update-task-completed', 'Admin\AssigneeController@updatetaskcompleted');
Route::post('/update-task-not-completed', 'Admin\AssigneeController@updatetasknotcompleted');
```

#### Controller Methods to Remove:
- All methods from `TasksController` (entire controller to be deleted)

#### References to Clean:
- Task references in `app/Http/Controllers/Admin/ReportController.php`
- Task references in `app/Services/DashboardService.php`
- Task references in `app/Console/Kernel.php`
- Task references in view files (already commented out, but verify)

#### Partner/Client Task Routes to Remove:
```php
// Lines 658-663 (approximately)
Route::post('/partner/addtask', [PartnersController::class, 'addtask']);
Route::get('/partner/get-tasks', [PartnersController::class, 'gettasks']);
Route::get('/partner/get-task-detail', [PartnersController::class, 'taskdetail']);
Route::post('/partner/savecomment', [PartnersController::class, 'savecomment']);
Route::get('/change-task-status', [PartnersController::class, 'changetaskstatus']);
Route::get('/change-task-priority', [PartnersController::class, 'changetaskpriority']);
```

#### Controller Methods to Remove from PartnersController:
- `addtask()`
- `gettasks()`
- `taskdetail()`
- `savecomment()` (if task-related)
- `changetaskstatus()`
- `changetaskpriority()`

---

### 2. Tax Management System Removal

**Status:** Feature removed, but files still exist

#### Files to Delete:
- `app/Models/TaxRate.php` ✅ (Still exists - needs deletion)

#### View Files to Delete:
- `resources/views/Admin/settings/taxrates.blade.php` ✅ (Still exists)

#### Routes to Remove from `routes/web.php`:
- Search for tax-related routes (may be in TaxController)

#### Controller Methods to Remove:
- Tax-related methods from `InvoiceController.php`
- Tax-related methods from `AdminController.php`
- Entire `TaxController.php` if it only handles tax rates

#### References to Clean:
- Tax references in `resources/views/Admin/invoice/create.blade.php`
- Tax references in `resources/views/invoices/invoice.blade.php`
- Tax references in `app/Http/Controllers/Admin/InvoiceController.php`
- Tax references in `app/Http/Controllers/Admin/AdminController.php`
- Tax references in `app/Console/Commands/CronJob.php`

---

### 3. Enquiry Source System Removal

**Status:** Feature removed, but files still exist

#### Files to Delete:
- `app/Http/Controllers/Admin/EnquirySourceController.php` ✅ (Still exists - needs deletion)
- `app/Models/EnquirySource.php` ✅ (Still exists - needs deletion)
- `app/Models/NatureOfEnquiry.php` ✅ (Still exists - needs deletion)

#### View Files to Delete:
- `resources/views/Admin/enquirysource/index.blade.php` ✅ (Still exists)
- All enquiry source view files

#### Routes to Remove from `routes/web.php`:
```php
// Lines 611-616 (approximately)
Route::get('/enquirysource', 'Admin\EnquirySourceController@index')->name('admin.enquirysource.index');
Route::get('/enquirysource/create', 'Admin\EnquirySourceController@create')->name('admin.enquirysource.create');
Route::post('enquirysource/store', 'Admin\EnquirySourceController@store')->name('admin.enquirysource.store');
Route::get('/enquirysource/edit/{id}', 'Admin\EnquirySourceController@edit')->name('admin.enquirysource.edit');
Route::post('/enquirysource/edit', 'Admin\EnquirySourceController@edit')->name('admin.enquirysource.update');
```

#### Controller Methods to Remove:
- All methods from `EnquirySourceController` (entire controller to be deleted)

#### References to Clean:
- EnquirySource references in `app/Http/Controllers/Admin/AssigneeController.php`
- NatureOfEnquiry references in `app/Http/Controllers/Admin/ClientsController.php`
- EnquirySource references in view files

---

### 4. Promo Code System Removal

**Status:** Feature removed, but files still exist

#### Files to Delete:
- `app/Http/Controllers/Admin/PromoCodeController.php` ✅ (Still exists - needs deletion)
- `app/Models/PromoCode.php` ✅ (Still exists - needs deletion)

#### View Files to Delete:
- All promo code view files in `resources/views/Admin/promocode/` (if exists)

#### Routes to Remove from `routes/web.php`:
- Search for promo code routes

#### Controller Methods to Remove:
- All methods from `PromoCodeController` (entire controller to be deleted)

#### References to Clean:
- PromoCode references in `resources/views/Admin/clients/detail.blade.php`
- PromoCode references in `resources/views/Admin/clients/addclientmodal.blade.php`
- PromoCode references in storage framework views (will be regenerated)

---

### 5. Invoice Schedule System Removal

**Status:** Feature removed, but files still exist

#### Files to Delete:
- `app/Models/InvoiceSchedule.php` ✅ (Still exists - needs deletion)
- `app/Models/ScheduleItem.php` ✅ (Still exists - needs deletion)
- `resources/views/Admin/invoice/invoiceschedules.blade.php` ✅ (Still exists)
- `resources/views/emails/paymentschedules.blade.php` ✅ (Still exists)
- `resources/views/Admin/invoice/ScheduleItem.php` (if exists, seems like wrong location)

#### Routes to Remove from `routes/web.php`:
- All invoice schedule routes (already removed per changelog, but verify)

#### Controller Methods to Remove:
- All schedule-related methods from `InvoiceController.php` (already removed per changelog, but verify)

#### References to Clean:
- InvoiceSchedule references in `app/Http/Controllers/Admin/InvoiceController.php`
- InvoiceSchedule references in `app/Http/Controllers/Admin/AdminController.php`
- Schedule references in `resources/views/Admin/clients/applicationdetail.blade.php`
- Schedule references in `resources/views/Agent/clients/applicationdetail.blade.php`

---

### 6. Quotations System Removal

**Status:** Feature removed, but routes and files still exist

#### Files to Delete:
- `app/Http/Controllers/Admin/QuotationsController.php` (verify if exists)
- `app/Models/Quotation.php` (verify if exists)
- `app/Models/QuotationInfo.php` (verify if exists)

#### View Files to Delete:
- `resources/views/Admin/quotations/index.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/create.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/edit.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/detail.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/archived.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/template/index.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/template/create.blade.php` ✅ (Still exists)
- `resources/views/Admin/quotations/template/edit.blade.php` ✅ (Still exists)
- `resources/views/emails/quotaion.blade.php` ✅ (Still exists - typo in filename)

#### Routes to Remove from `routes/web.php`:
```php
// Lines 368-394 (approximately)
Route::get('/quotations', 'Admin\QuotationsController@index')->name('admin.quotations.index');
Route::get('/quotations/client', 'Admin\QuotationsController@client')->name('admin.quotations.client');
Route::get('/quotations/client/create/{id}', 'Admin\QuotationsController@create')->name('admin.quotations.create');
Route::post('/quotations/store', 'Admin\QuotationsController@store')->name('admin.quotations.store');
Route::get('/quotations/edit/{id}', 'Admin\QuotationsController@edit')->name('admin.quotations.edit');
Route::post('/quotations/edit', 'Admin\QuotationsController@edit');
Route::get('/quotations/template', 'Admin\QuotationsController@template')->name('admin.quotations.template.index');
Route::get('/quotations/template/create', 'Admin\QuotationsController@template_create')->name('admin.quotations.template.create');
Route::post('/quotations/template/store', 'Admin\QuotationsController@template_store')->name('admin.quotations.template.store');
Route::get('/quotations/template/edit/{id}', 'Admin\QuotationsController@template_edit')->name('admin.quotations.template.edit');
Route::post('/quotations/template/edit', 'Admin\QuotationsController@template_edit');
Route::get('/quotation/detail/{id}', 'Admin\QuotationsController@quotationDetail');
Route::get('/quotation/preview/{id}', 'Admin\QuotationsController@quotationpreview');
Route::get('quotations/archived', 'Admin\QuotationsController@archived')->name('admin.quotations.archived');
Route::get('quotations/changestatus', 'Admin\QuotationsController@changestatus')->name('admin.quotations.changestatus');
Route::post('quotations/sendmail', 'Admin\QuotationsController@sendmail')->name('admin.quotations.sendmail');
Route::get('getpartner', 'Admin\AdminController@getpartner')->name('admin.quotations.getpartner');
Route::get('getpartnerbranch', 'Admin\AdminController@getpartnerbranch')->name('admin.quotations.getpartnerbranch');
Route::get('getsubjects', 'Admin\AdminController@getsubjects');
Route::get('getbranchproduct', 'Admin\AdminController@getbranchproduct')->name('admin.quotations.getbranchproduct');
Route::get('getproduct', 'Admin\AdminController@getproduct')->name('admin.quotations.getproduct');
Route::get('getbranch', 'Admin\AdminController@getbranch')->name('admin.quotations.getbranch');
Route::get('getnewPartnerbranch', 'Admin\AdminController@getnewPartnerbranch')->name('admin.quotations.getnewPartnerbranch');
```

#### Controller Methods to Remove:
- All methods from `QuotationsController` (entire controller to be deleted)
- Quotation-related helper methods from `AdminController.php`:
  - `getpartner()`
  - `getpartnerbranch()`
  - `getsubjects()` (if quotation-specific)
  - `getbranchproduct()`
  - `getproduct()` (if quotation-specific)
  - `getbranch()` (if quotation-specific)
  - `getnewPartnerbranch()`

#### References to Clean:
- Quotation references in `resources/views/Admin/clients/detail.blade.php`
- Quotation references in `resources/views/Elements/Admin/left-side-bar.blade.php`
- Quotation references in `resources/views/Admin/products/addproductmodal.blade.php`
- Quotation references in `resources/views/Admin/users/view.blade.php`
- Quotation references in `resources/views/Admin/userrole/edit.blade.php`
- Quotation references in `resources/views/Admin/userrole/create.blade.php`
- Quotation references in `routes/agent.php`

---

### 7. Email Option Removal from Partners

**Status:** Planned but not implemented

#### Files to Modify:
- `resources/views/Admin/partners/index.blade.php`
- `resources/views/Admin/partners/inactive.blade.php`
- `resources/views/Admin/partners/edit.blade.php`
- `resources/views/Admin/partners/create.blade.php`
- `app/Http/Controllers/Admin/PartnersController.php`

#### JavaScript to Remove:
- Event handler for `.partneremail` class
- Email option from action dropdown menu

#### UI Elements to Remove:
- "Email" option from Action dropdown in Partners Manager (Active tab)
- "Email" option from Action dropdown in Partners Manager (Inactive tab)

**Note:** Bulk email functionality (via checkboxes) should be preserved as it's a separate feature.

---

### 8. Import Functionality Cleanup

**Status:** Methods removed, but verify routes are cleaned

#### Routes to Verify Removal:
- `POST /applications-import` (Line 677 - still exists, needs removal)
- `POST /products-import` (Line 339 - still exists, needs removal)
- `POST /partners-import` (verify if exists)

#### Controller Methods to Verify Removal:
- `ApplicationsController::import()` (verify removed)
- `PartnersController::import()` (verify removed)
- `ProductsController::import()` (verify removed)

---

### 9. Applications Detail Route Cleanup

**Status:** Detail page removed, but route may still exist

#### Routes to Remove/Modify:
```php
// Line 437 (approximately)
Route::get('/applications/detail/{id}', 'Admin\ApplicationsController@detail')->name('admin.applications.detail');
```
- Should redirect to Clients detail view or be removed

#### Controller Methods to Verify Removal:
- `ApplicationsController::detail()` (verify removed)

---

### 10. Obsolete Model Files

**Status:** Models removed, but verify files are deleted

#### Files to Verify Deletion:
- `app/Models/FreeDownload.php` (if exists)
- `app/Models/PasswordResetLink.php` (if exists)
- `app/Models/VerifyUser.php` (if exists)
- `app/Models/ApplicationNote.php` (if exists)

---

## Files to Delete

### Controllers (7 files):
1. `app/Http/Controllers/Admin/TasksController.php`
2. `app/Http/Controllers/Admin/EnquirySourceController.php`
3. `app/Http/Controllers/Admin/PromoCodeController.php`
4. `app/Http/Controllers/Admin/QuotationsController.php` (verify)
5. `app/Http/Controllers/Admin/TaxController.php` (if exists and only handles tax rates)

### Models (9 files):
1. `app/Models/Task.php`
2. `app/Models/TaskLog.php`
3. `app/Models/ToDoGroup.php`
4. `app/Models/TaxRate.php`
5. `app/Models/EnquirySource.php`
6. `app/Models/NatureOfEnquiry.php`
7. `app/Models/PromoCode.php`
8. `app/Models/InvoiceSchedule.php`
9. `app/Models/ScheduleItem.php`

### View Files (15+ files):
1. `resources/views/Admin/tasks/index.blade.php`
2. `resources/views/Admin/settings/taxrates.blade.php`
3. `resources/views/Admin/enquirysource/index.blade.php`
4. `resources/views/Admin/invoice/invoiceschedules.blade.php`
5. `resources/views/emails/paymentschedules.blade.php`
6. `resources/views/Admin/quotations/index.blade.php`
7. `resources/views/Admin/quotations/create.blade.php`
8. `resources/views/Admin/quotations/edit.blade.php`
9. `resources/views/Admin/quotations/detail.blade.php`
10. `resources/views/Admin/quotations/archived.blade.php`
11. `resources/views/Admin/quotations/template/index.blade.php`
12. `resources/views/Admin/quotations/template/create.blade.php`
13. `resources/views/Admin/quotations/template/edit.blade.php`
14. `resources/views/emails/quotaion.blade.php`
15. All other task, tax, enquiry, promo code view files

### Console Commands (if exist):
- `app/Console/Commands/CompleteTaskRemoval.php`
- `app/Console/Commands/InPersonCompleteTaskRemoval.php`

---

## Routes to Remove

### Task Routes (18 routes):
- All task management routes (lines ~417-432)
- Partner/Client task routes (lines ~658-663)
- Task report routes (lines ~712-713)
- Assignee task routes (lines ~759-760)

### Tax Routes:
- All tax rate management routes (search for tax-related routes)

### Enquiry Source Routes (5 routes):
- Lines ~611-616

### Promo Code Routes:
- Search for promo code routes

### Quotation Routes (18+ routes):
- Lines ~367-395

### Import Routes (3 routes):
- `POST /applications-import` (line ~677)
- `POST /products-import` (line ~339)
- `POST /partners-import` (verify)

### Applications Detail Route (1 route):
- `GET /applications/detail/{id}` (line ~437)

**Total Routes to Remove:** ~44+ routes

---

## Controller Methods to Remove

### From PartnersController:
- `addtask()`
- `gettasks()`
- `taskdetail()`
- `savecomment()` (if task-related)
- `changetaskstatus()`
- `changetaskpriority()`
- `partneremail()` or email-related method (if individual email)

### From AdminController:
- Tax-related methods
- Quotation helper methods:
  - `getpartner()`
  - `getpartnerbranch()`
  - `getsubjects()` (if quotation-specific)
  - `getbranchproduct()`
  - `getproduct()` (if quotation-specific)
  - `getbranch()` (if quotation-specific)
  - `getnewPartnerbranch()`

### From InvoiceController:
- Tax-related methods (verify already removed)
- Schedule-related methods (verify already removed)

### From ReportController:
- `personal_task()` - Personal task report method
- `office_task()` - Office task report method

### From AssigneeController:
- `updatetaskcompleted()` - Update task to completed
- `updatetasknotcompleted()` - Update task to not completed
- Task-related appointment methods (if any)

### From ApplicationsController:
- `import()` (verify already removed)
- `detail()` (verify already removed)

### From ProductsController:
- `import()` (verify already removed)

---

## Model References to Clean

### Files with Task References:
- `app/Http/Controllers/Admin/ReportController.php`
- `app/Services/DashboardService.php`
- `app/Console/Kernel.php`
- View files (verify all commented out)

### Files with Tax References:
- `app/Http/Controllers/Admin/InvoiceController.php`
- `app/Http/Controllers/Admin/AdminController.php`
- `app/Console/Commands/CronJob.php`
- `resources/views/Admin/invoice/create.blade.php`
- `resources/views/invoices/invoice.blade.php`

### Files with EnquirySource References:
- `app/Http/Controllers/Admin/AssigneeController.php`
- `app/Http/Controllers/Admin/ClientsController.php`
- View files

### Files with PromoCode References:
- `resources/views/Admin/clients/detail.blade.php`
- `resources/views/Admin/clients/addclientmodal.blade.php`

### Files with InvoiceSchedule References:
- `app/Http/Controllers/Admin/InvoiceController.php`
- `app/Http/Controllers/Admin/AdminController.php`
- `resources/views/Admin/clients/applicationdetail.blade.php`
- `resources/views/Agent/clients/applicationdetail.blade.php`

### Files with Quotation References:
- `resources/views/Admin/clients/detail.blade.php`
- `resources/views/Elements/Admin/left-side-bar.blade.php`
- `resources/views/Admin/products/addproductmodal.blade.php`
- `resources/views/Admin/users/view.blade.php`
- `resources/views/Admin/userrole/edit.blade.php`
- `resources/views/Admin/userrole/create.blade.php`
- `routes/agent.php`

---

## View Files to Remove

### Task Views:
- `resources/views/Admin/tasks/` (entire directory)

### Tax Views:
- `resources/views/Admin/settings/taxrates.blade.php`

### Enquiry Source Views:
- `resources/views/Admin/enquirysource/` (entire directory)

### Promo Code Views:
- `resources/views/Admin/promocode/` (if exists)

### Invoice Schedule Views:
- `resources/views/Admin/invoice/invoiceschedules.blade.php`
- `resources/views/emails/paymentschedules.blade.php`

### Quotation Views:
- `resources/views/Admin/quotations/` (entire directory)
- `resources/views/emails/quotaion.blade.php`

---

## JavaScript/CSS Cleanup

### JavaScript Handlers to Remove:
- Task-related event handlers (verify already removed)
- Tax-related handlers (if any)
- Enquiry source handlers (if any)
- Promo code handlers (verify already removed)
- Invoice schedule handlers (verify already removed)
- Quotation handlers (if any)
- `.partneremail` handler (for individual email option)

### CSS Classes to Remove:
- Task-related CSS classes (if any)
- Tax-related CSS classes (if any)
- Other feature-specific CSS classes

---

## Database Considerations

**Important:** Per changelog policy, database tables are **preserved** (not removed). Only code files and references should be removed.

### Tables to Preserve:
- `tasks`
- `task_logs`
- `todo_groups`
- `tax_rates`
- `enquiry_sources`
- `nature_of_enquiries`
- `promo_codes`
- `invoice_schedules`
- `schedule_items`
- `cashbacks`
- `quotations`
- `quotation_info`

**Note:** Migrations for table removal may exist but should not be executed.

---

## Implementation Order

### Phase 1: Complete Feature Removals (High Priority)
1. **Task Management System**
   - Delete controller, models, views
   - Remove routes
   - Clean references

2. **Tax Management System**
   - Delete model, views
   - Remove routes
   - Clean references

3. **Enquiry Source System**
   - Delete controller, models, views
   - Remove routes
   - Clean references

4. **Promo Code System**
   - Delete controller, model, views
   - Remove routes
   - Clean references

5. **Invoice Schedule System**
   - Delete models, views
   - Verify routes removed
   - Clean references

6. **Quotations System**
   - Delete controller, models, views
   - Remove routes
   - Clean references

### Phase 2: Partial Feature Cleanup (Medium Priority)
7. **Email Option from Partners**
   - Remove individual email option
   - Preserve bulk email

8. **Import Functionality**
   - Verify routes removed
   - Verify methods removed

9. **Applications Detail**
   - Verify route removed/redirected
   - Verify method removed

### Phase 3: Reference Cleanup (Low Priority)
10. **Model References**
    - Clean all Task references
    - Clean all Tax references
    - Clean all EnquirySource references
    - Clean all PromoCode references
    - Clean all InvoiceSchedule references
    - Clean all Quotation references

11. **JavaScript/CSS Cleanup**
    - Remove dead handlers
    - Remove unused CSS

---

## Verification Checklist

### Before Implementation:
- [ ] Backup database
- [ ] Backup codebase
- [ ] Review all files listed in this plan
- [ ] Verify no critical dependencies on removed features

### After Each Phase:
- [ ] Run `php artisan route:list` to verify routes removed
- [ ] Check for linter errors
- [ ] Test affected pages
- [ ] Verify no broken links
- [ ] Check browser console for JavaScript errors

### Final Verification:
- [ ] All listed files deleted
- [ ] All listed routes removed
- [ ] All listed methods removed
- [ ] No broken references
- [ ] No linter errors
- [ ] Application runs without errors
- [ ] Database tables preserved
- [ ] Documentation updated

---

## Estimated Impact

### Files to Delete:
- **Controllers:** 5-7 files
- **Models:** 9 files
- **Views:** 15+ files
- **Total:** ~31+ files

### Routes to Remove:
- **~40+ routes**

### Lines of Code:
- **Estimated:** 5,000+ lines of code to remove

### Risk Level:
- **Medium** - Systematic removal with proper verification

---

## Notes

1. **Storage Framework Views:** Files in `storage/framework/views/` will be regenerated automatically, so don't worry about cleaning those.

2. **Commented Code:** Some files may have commented-out code blocks. Consider removing these for cleaner codebase.

3. **Test Coverage:** After removal, ensure all tests still pass (if test suite exists).

4. **Documentation:** Update any documentation that references removed features.

5. **Migration Files:** Don't execute table drop migrations. Keep them for reference but don't run them.

---

## Next Steps

1. Review this plan with the team
2. Create backup of codebase
3. Start with Phase 1 removals
4. Test after each phase
5. Document any issues encountered
6. Update changelog after completion

---

**Document Status:** ⏳ Planning Complete - Ready for Review  
**Last Updated:** January 3, 2026  
**Next Action:** Review and approve plan before implementation

