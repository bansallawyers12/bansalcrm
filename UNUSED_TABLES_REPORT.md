# Unused Database Tables Report

Generated: Analysis of database tables vs codebase usage

## Summary

- **Total tables analyzed**: 81 (excluding system tables)
- **Used tables**: 72
- **Potentially unused tables**: 13 (after removing false positives)

---

## ⚠️ Potentially Unused Tables

The following tables were not found to be referenced in the codebase:

### 1. `api_tokens`
- **Row count**: 3
- **Status**: May be used for API authentication
- **Recommendation**: Check if API functionality is active

### 2. `check_partners`
- **Row count**: 3
- **Status**: Unknown purpose
- **Recommendation**: Investigate purpose before removal

### 3. `currencies`
- **Row count**: 6
- **Status**: May be referenced in old code or views
- **Note**: Contact model has a `currencydata()` relationship that references Currency model, but no Currency model file found
- **Recommendation**: Check if currency functionality is needed

### 4. `invoice_schedules`
- **Row count**: 6
- **Status**: Not referenced in code
- **Recommendation**: May be part of unused invoice scheduling feature

### 5. `postcode_ranges`
- **Row count**: 480
- **Status**: Not referenced in code
- **Recommendation**: May be used for address/postcode validation

### 6. `quotation_infos`
- **Row count**: 13
- **Status**: Related to removed quotations feature
- **Note**: Quotations feature was removed (January 2026) per `routes/web.php`
- **Recommendation**: Safe to remove if quotations feature is confirmed removed

### 7. `service_fee_option_types`
- **Row count**: 1
- **Status**: Not referenced in code
- **Recommendation**: May be part of unused service fee feature

### 8. `service_fee_options`
- **Row count**: 1
- **Status**: Not referenced in code
- **Recommendation**: May be part of unused service fee feature

### 9. `suburbs`
- **Row count**: 18,530
- **Status**: Not referenced in code
- **Recommendation**: May be used for address/geographic data

### 10. `task_logs`
- **Row count**: 117
- **Status**: Not referenced in code
- **Note**: `tasks` table is used, but `task_logs` is not
- **Recommendation**: May be logging table for tasks feature

### 11. `tax_rates`
- **Row count**: 7
- **Status**: Not referenced in code
- **Recommendation**: May be part of tax calculation feature

### 12. `taxes`
- **Row count**: 1
- **Status**: ✅ **ACTUALLY USED** - Used via Tax model
- **Used in**: `TaxController.php`, invoice views
- **Note**: This was a false positive from the script

### 13. `to_do_groups`
- **Row count**: 5
- **Status**: Not referenced in code
- **Recommendation**: May be part of unused todo/grouping feature

### 14. `verify_users`
- **Row count**: 22
- **Status**: Not referenced in code
- **Recommendation**: May be used for user verification feature

---

## ✅ False Positives (Actually Used)

The following tables were incorrectly flagged as unused but ARE actually used:

1. **`branches`** - Used via `Branch` model in:
   - `BranchesController.php`
   - Multiple views and controllers

2. **`categories`** - Used via `Category` model in:
   - `MasterCategoryController.php`
   - Multiple views

3. **`cities`** - Used via `City` model in:
   - `AdminController.php`
   - Multiple views

4. **`countries`** - Used via `Country` model in:
   - `AdminController.php`
   - Multiple views (products, partners, clients)

5. **`enquiries`** - Used via `Enquiry` model in:
   - `EnquireController.php`

6. **`checkin_histories`** - Used via `CheckinHistory` model in:
   - `OfficeVisitController.php`

7. **`sub_categories`** - Used via `SubCategory` model in:
   - Multiple views and controllers

8. **`taxes`** - Used via `Tax` model in:
   - `TaxController.php`
   - Invoice views (commission-invoice, general-invoice, edit)

---

## 🔍 Tables Requiring Further Investigation

These tables have models but may not be actively used:

1. **`taxes`** - Tax model exists but table may not be used
2. **`currencies`** - Referenced in Contact model relationship but no Currency model found

---

## 📋 Recommendations

### Before Removing Any Tables:

1. **Backup the database** - Always backup before removing tables
2. **Check foreign key constraints** - Some tables may be referenced by foreign keys
3. **Verify feature status** - Some tables may be for features that are planned or partially implemented
4. **Check for scheduled jobs/cron** - Some tables may be used by background processes
5. **Review migration history** - Check when tables were created and why

### Safe to Remove (After Verification):

- `quotation_infos` - If quotations feature is confirmed removed
- `service_fee_option_types` & `service_fee_options` - If service fee feature is not used

### Requires Investigation:

- `api_tokens` - Check if API is used
- `currencies` - Check if currency feature is needed (Contact model references it)
- `postcode_ranges` & `suburbs` - May be used for address validation
- `tax_rates` - Check if tax rate calculation is used (Tax model uses `taxes` table)
- `task_logs` - Check if task logging is needed
- `to_do_groups` - Check if todo grouping feature exists
- `verify_users` - Check if user verification is active

---

## Script Used

The analysis was performed using `check_unused_tables.php` which:
- Connects to the database and lists all tables
- Searches the codebase for model files, DB::table() calls, and SQL queries
- Checks migrations and model `$table` properties
- Reports tables with no references found

**Note**: The script may have false positives/negatives. Manual verification is recommended.

---

## Next Steps

1. Review each "unused" table individually
2. Check database foreign key constraints
3. Verify with team if features are still needed
4. Create database backup before any removals
5. Consider creating migrations to drop confirmed unused tables

