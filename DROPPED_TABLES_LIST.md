# List of Recently Dropped Tables

This document lists all database tables that have been dropped in recent migrations.

## Migration Summary

Three migration files have been created to drop tables:

1. **2026_01_04_142424_drop_fee_options_tables.php** (January 4, 2026)
2. **2026_01_05_144959_drop_unused_tables.php** (January 5, 2026)
3. **2026_01_05_145639_drop_additional_tables.php** (January 5, 2026)

---

## Complete List of Dropped Tables

### Migration 1: `2026_01_04_142424_drop_fee_options_tables.php`

**Date:** January 4, 2026  
**Reason:** Replaced by `application_fee_options` and `application_fee_option_types` tables

1. **`fee_option_types`** (child table)
   - Replaced by application-specific fee customization

2. **`fee_options`** (parent table)
   - Replaced by application-specific fee customization

---

### Migration 2: `2026_01_05_144959_drop_unused_tables.php`

**Date:** January 5, 2026  
**Reason:** Unused tables identified in UNUSED_TABLES_REPORT.md (serial 1-13)

3. **`service_fee_option_types`**
   - Row count: 1
   - Status: Not referenced in code

4. **`service_fee_options`**
   - Row count: 1
   - Status: Not referenced in code

5. **`quotation_infos`**
   - Row count: 13
   - Status: Related to removed quotations feature

6. **`invoice_schedules`**
   - Row count: 6
   - Status: Not referenced in code

7. **`task_logs`**
   - Row count: 117
   - Status: Not referenced in code

8. **`tax_rates`**
   - Row count: 7
   - Status: Not referenced in code

9. **`taxes`**
   - Row count: 1
   - ⚠️ **WARNING:** This table was actually used by TaxController.php and invoice views
   - Note: Also appears in Migration 3

10. **`to_do_groups`**
    - Row count: 5
    - Status: Not referenced in code

11. **`suburbs`**
    - Row count: 18,530
    - Status: Not referenced in code

12. **`postcode_ranges`**
    - Row count: 480
    - Status: Not referenced in code

13. **`currencies`**
    - Row count: 6
    - Status: Not referenced in code

14. **`check_partners`**
    - Row count: 3
    - Status: Unknown purpose

15. **`api_tokens`**
    - Row count: 3
    - Status: May be used for API authentication

---

### Migration 3: `2026_01_05_145639_drop_additional_tables.php`

**Date:** January 5, 2026  
**Reason:** Additional tables identified for removal

⚠️ **WARNING:** These tables were actually USED in the codebase and removing them may break functionality.

16. **`cities`**
    - Row count: ~48
    - ⚠️ **WARNING:** Used by City model in AdminController.php
    - Status: Actually used in codebase

17. **`enquiries`**
    - Row count: ~1,475
    - ⚠️ **WARNING:** Used by Enquiry model in EnquireController.php
    - Status: Actually used in codebase

18. **`checkin_histories`**
    - Row count: ~95,527
    - ⚠️ **WARNING:** Used by CheckinHistory model in OfficeVisitController.php
    - Status: Actually used in codebase

19. **`taxes`** (duplicate)
    - ⚠️ **WARNING:** Also dropped in Migration 2
    - ⚠️ **WARNING:** Used by Tax model in TaxController.php and invoice views
    - Status: Actually used in codebase

---

## Summary Statistics

- **Total tables dropped:** 19 unique tables (note: `taxes` appears in both Migration 2 and 3)
- **Total unique tables:** 18 tables
- **Tables with warnings:** 4 tables (cities, enquiries, checkin_histories, taxes)
- **Total rows affected:** ~116,000+ rows

---

## Tables Excluded from Removal

The following table was identified as unused but **NOT** dropped per user request:

- **`verify_users`** (22 rows)
  - Status: Not referenced in code
  - Action: Excluded from migration per user request

---

## Important Notes

1. **Backup Required:** All migrations include warnings that tables are not recreated in the `down()` method. If rollback is needed, restore from database backup.

2. **Functionality Impact:** Migration 3 drops tables that are actually used in the codebase. Ensure code has been updated before running this migration.

3. **Duplicate Removal:** The `taxes` table appears in both Migration 2 and Migration 3, which may cause issues if migrations are run multiple times.

4. **Data Loss:** Significant data was removed:
   - `checkin_histories`: ~95,527 rows
   - `suburbs`: 18,530 rows
   - `postcode_ranges`: 480 rows
   - `enquiries`: ~1,475 rows

---

## Migration Execution Order

If these migrations haven't been run yet, they should be executed in this order:

1. `2026_01_04_142424_drop_fee_options_tables.php`
2. `2026_01_05_144959_drop_unused_tables.php`
3. `2026_01_05_145639_drop_additional_tables.php`

---

## Recommendations

1. **Verify Code Updates:** Before running Migration 3, ensure all code referencing `cities`, `enquiries`, `checkin_histories`, and `taxes` has been updated or removed.

2. **Database Backup:** Always backup the database before running these migrations.

3. **Test Environment:** Run these migrations in a test environment first to verify no functionality breaks.

4. **Review Warnings:** Pay special attention to tables marked with ⚠️ warnings as they may break existing functionality.

