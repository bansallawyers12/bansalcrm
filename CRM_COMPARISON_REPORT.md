# CRM Comparison Report: bansalcrm vs bansalcrm2

**Date:** January 2026  
**Purpose:** Identify files that should be REMOVED from bansalcrm to mirror bansalcrm2  
**Status:** REPORT ONLY - No deletions performed

---

## Executive Summary

This report compares `bansalcrm` (current) with `bansalcrm2` (target) to identify files that should be removed to make bansalcrm mirror bansalcrm2.

**Key Findings:**
- **Controllers to Remove:** 0 (bansalcrm2 uses ActionController, bansalcrm uses AssigneeController - both serve same purpose)
- **Models to Remove:** Several obsolete models found
- **Views to Remove:** Multiple view directories and files
- **Routes:** Some routes need cleanup

---

## 1. CONTROLLERS COMPARISON

### Controllers in bansalcrm but NOT in bansalcrm2:
✅ **KEEP** - These are feature controllers that should remain:
- `AssigneeController.php` - **KEEP** (bansalcrm2 has `ActionController` which serves same purpose, but AssigneeController is more feature-complete)
- `ChecklistController.php` - **KEEP** (feature controller)
- `CrmEmailTemplateController.php` - **KEEP** (feature controller)
- `DocumentChecklistController.php` - **KEEP** (feature controller)
- `EmailController.php` - **KEEP** (feature controller)
- `EnquireController.php` - **KEEP** (enquiry management)
- `FeeTypeController.php` - **KEEP** (feature controller)
- `LeadServiceController.php` - **KEEP** (feature controller)
- `MasterCategoryController.php` - **KEEP** (feature controller)
- `PartnerTypeController.php` - **KEEP** (feature controller)
- `ProductTypeController.php` - **KEEP** (feature controller)
- `ProfileController.php` - **KEEP** (feature controller)
- `SourceController.php` - **KEEP** (feature controller)
- `SubjectAreaController.php` - **KEEP** (feature controller)
- `SubjectController.php` - **KEEP** (feature controller)
- `TagController.php` - **KEEP** (feature controller)
- `TaxController.php` - **KEEP** (feature controller - different from TaxRate)
- `VisaTypeController.php` - **KEEP** (feature controller)
- `WorkflowController.php` - **KEEP** (feature controller)

### Controllers in bansalcrm2 but NOT in bansalcrm:
- `ActionController.php` - **NOTE:** bansalcrm uses `AssigneeController` instead (same functionality)

**RECOMMENDATION:** Keep all controllers in bansalcrm. The AssigneeController vs ActionController difference is acceptable.

---

## 2. MODELS COMPARISON

### Models in bansalcrm but NOT in bansalcrm2:
❌ **REMOVE** - Obsolete models:
- `AttachFile.php` - **REMOVE** (not in bansalcrm2)
- `Attachment.php` - **REMOVE** (not in bansalcrm2)
- `Enquiry.php` - **KEEP** (used by EnquireController)
- `FeeOption.php` - **REMOVE** (not in bansalcrm2, replaced by ApplicationFeeOption)
- `FeeOptionType.php` - **REMOVE** (not in bansalcrm2, replaced by ApplicationFeeOptionType)
- `Item.php` - **REMOVE** (not in bansalcrm2)
- `OnlineForm.php` - **KEEP** (used for client form data storage)
- `ProductAreaLevel.php` - **REMOVE** (not in bansalcrm2)
- `RepresentingPartner.php` - **REMOVE** (not in bansalcrm2)
- `ServiceFeeOption.php` - **REMOVE** (not in bansalcrm2)
- `ServiceFeeOptionType.php` - **REMOVE** (not in bansalcrm2)
- `Tax.php` - **KEEP** (feature model, different from TaxRate)
- `Template.php` - **REMOVE** (not in bansalcrm2)
- `TemplateInfo.php` - **REMOVE** (not in bansalcrm2)
- `User.php` - **REMOVE** (not in bansalcrm2, uses Admin model instead)

### Models in bansalcrm2 but NOT in bansalcrm:
- None (bansalcrm has more models, which is expected for feature controllers)

**RECOMMENDATION:** Remove 11 obsolete models listed above.

---

## 3. VIEWS COMPARISON

### View Directories/Files in bansalcrm but NOT in bansalcrm2:

#### ❌ **REMOVE** - Obsolete view directories:
1. **`resources/views/Admin/assignee/`** - **REMOVE** (bansalcrm2 uses `action/` instead)
   - `activities_completed.blade.php`
   - `activities.blade.php`
   - `assign_by_me.blade.php`
   - `assign_to_me.blade.php`
   - `completed.blade.php`
   - `index.blade.php`
   
   **NOTE:** bansalcrm2 has `resources/views/Admin/action/` with similar files. Consider renaming or removing assignee views.

2. **`resources/views/Admin/prospects/`** - **REMOVE** (empty directory, feature removed)
   - Directory exists but is empty

3. **`resources/views/Admin/feature/promocode/`** - **REMOVE** (Promo Code System removed)
   - `create.blade.php`
   - `edit.blade.php`
   - `index.blade.php`

4. **`resources/views/Admin/reports/personal-task-report.blade.php`** - **REMOVE** (Task System removed)
   - File should not exist

5. **`resources/views/Admin/reports/office-task-report.blade.php`** - **REMOVE** (Task System removed)
   - File should not exist

6. **`resources/views/Admin/reports/clientrandomlyselectmonthly.blade.php`** - **KEEP** (exists only in bansalcrm, may be new feature)

### View Directories in bansalcrm2 but NOT in bansalcrm:
- `resources/views/Admin/action/` - **NOTE:** bansalcrm uses `assignee/` instead

**RECOMMENDATION:** 
- Remove prospects directory (empty)
- Remove promocode views (feature removed)
- Remove task report views
- Consider keeping assignee views OR migrating to action views (check functionality first)

---

## 4. ROUTES COMPARISON

### Routes in bansalcrm that reference removed features:
❌ **CLEANUP NEEDED:**
- `/admin/prospects` route (line 307) - **REMOVE** (Prospects feature removed)
- Routes referencing task reports - **VERIFY REMOVAL**
- Routes referencing promocode - **VERIFY REMOVAL**

### Routes in bansalcrm2:
- Uses `ActionController` routes instead of `AssigneeController` routes
- No prospects routes
- No task report routes
- No promocode routes

**RECOMMENDATION:** Clean up routes referencing removed features.

---

## 5. ADDITIONAL FILES

### Files in bansalcrm that may need attention:

#### Console Commands:
- `app/Console/Commands/CompleteTaskRemoval.php` - **KEEP** (cleanup command)
- `app/Console/Commands/InPersonCompleteTaskRemoval.php` - **KEEP** (cleanup command)

#### Settings Views:
- `resources/views/Admin/settings/returnsetting.blade.php` - **KEEP** (exists in both)

---

## 6. SUMMARY OF FILES TO REMOVE

### Models to Remove (11 files):
1. `app/Models/AttachFile.php`
2. `app/Models/Attachment.php`
3. `app/Models/FeeOption.php`
4. `app/Models/FeeOptionType.php`
5. `app/Models/Item.php`
6. `app/Models/ProductAreaLevel.php`
7. `app/Models/RepresentingPartner.php`
8. `app/Models/ServiceFeeOption.php`
9. `app/Models/ServiceFeeOptionType.php`
10. `app/Models/Template.php`
11. `app/Models/TemplateInfo.php`
12. `app/Models/User.php`

### Views to Remove:
1. `resources/views/Admin/prospects/` (entire directory - empty)
2. `resources/views/Admin/feature/promocode/` (entire directory - 3 files)
3. `resources/views/Admin/reports/personal-task-report.blade.php`
4. `resources/views/Admin/reports/office-task-report.blade.php`

### Views to Consider (Assignee vs Action):
- `resources/views/Admin/assignee/` - **DECISION NEEDED:** 
  - Option A: Keep if AssigneeController is more feature-complete
  - Option B: Remove and migrate to action/ views if ActionController is preferred
  - **RECOMMENDATION:** Check functionality differences first

### Routes to Clean Up:
1. Remove `/admin/prospects` route
2. Verify task report routes are removed
3. Verify promocode routes are removed

---

## 7. FILES TO KEEP (Confirmed)

### Controllers:
- All feature controllers (Checklist, FeeType, Tag, etc.) - **KEEP**
- `AssigneeController` - **KEEP** (serves same purpose as ActionController)
- `EnquireController` - **KEEP** (enquiry management active)

### Models:
- `Enquiry.php` - **KEEP** (used by EnquireController)
- `OnlineForm.php` - **KEEP** (used for client form data)
- `Tax.php` - **KEEP** (feature model, different from TaxRate)

### Views:
- All feature views in `resources/views/Admin/feature/` (except promocode) - **KEEP**
- `resources/views/Admin/reports/clientrandomlyselectmonthly.blade.php` - **KEEP** (new feature)

---

## 8. ACTION ITEMS

### High Priority (Remove):
1. ✅ Remove 12 obsolete models
2. ✅ Remove prospects directory (empty)
3. ✅ Remove promocode views
4. ✅ Remove task report views
5. ✅ Clean up routes referencing removed features

### Medium Priority (Verify):
1. ⚠️ Verify AssigneeController vs ActionController functionality
2. ⚠️ Check if assignee views should migrate to action views
3. ⚠️ Verify all task-related routes are removed

### Low Priority (Documentation):
1. 📝 Update route documentation
2. 📝 Update changelog with removed files

---

## 9. NOTES

1. **AssigneeController vs ActionController:** Both serve similar purposes. bansalcrm2 uses ActionController, but bansalcrm's AssigneeController may have more features. Verify before removing.

2. **Feature Controllers:** bansalcrm has more feature controllers (Checklist, FeeType, Tag, etc.) which is expected and should be kept.

3. **Enquiry vs EnquirySource:** bansalcrm has EnquiryController (active) and EnquirySource (removed). This is correct.

4. **Tax vs TaxRate:** bansalcrm has Tax model (feature) and TaxRate (removed). This is correct.

5. **Prospects:** Directory exists but is empty - safe to remove.

---

## 10. VERIFICATION CHECKLIST

Before removing files, verify:
- [ ] No active routes reference removed models
- [ ] No controllers reference removed models
- [ ] No views reference removed models
- [ ] Database tables for removed models are handled (migrations)
- [ ] AssigneeController functionality matches ActionController
- [ ] All task-related code is removed
- [ ] All promocode-related code is removed

---

**END OF REPORT**

**Next Steps:**
1. Review this report
2. Verify AssigneeController vs ActionController
3. Execute removals after verification
4. Update documentation

