# S3 Migration Testing Guide

## Step-by-Step Testing Instructions

---

## Phase 1: Verify AWS S3 Configuration (5 minutes)

### 1.1 Check Environment Variables

Open your `.env` file and verify these variables exist and are correct:

```env
AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=us-east-1  # or your region
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket-name.s3.amazonaws.com
```

### 1.2 Test S3 Connection

Run this command in terminal:

```bash
php artisan tinker
```

Then test S3 connection:

```php
// Test if S3 is accessible
Storage::disk('s3')->exists('test.txt')

// Try to upload a test file
Storage::disk('s3')->put('test.txt', 'Hello World');

// Get the URL
Storage::disk('s3')->url('test.txt');

// Exit tinker
exit
```

**Expected Result:** No errors, URL returned successfully

---

## Phase 2: Test Invoice Attachments (15-20 minutes)

### Test 1: Create New Invoice with Single Attachment

**Steps:**
1. Login to admin panel
2. Navigate to **Invoices** → **Create Invoice**
3. Fill in required invoice details:
   - Select Client
   - Select Product/Service
   - Add invoice items
   - **Upload 1 PDF file** in attachments field
4. Click "Save"

**What to Verify:**
- ✅ Invoice created successfully
- ✅ No error messages
- ✅ File uploaded without errors

**Check S3 Bucket:**
1. Go to AWS S3 Console → Your Bucket → `invoices/` folder
2. Look for file named like: `1234567890_yourfile.pdf`
3. Verify file exists and has correct size

**Check Database:**
```sql
-- Run in MySQL/phpMyAdmin
SELECT id, client_id, attachments, created_at 
FROM invoices 
ORDER BY id DESC 
LIMIT 1;
```

**Expected Result:**
- `attachments` column contains full S3 URL like:
  ```
  https://your-bucket.s3.region.amazonaws.com/invoices/1234567890_file.pdf
  ```

---

### Test 2: Create Invoice with Multiple Attachments

**Steps:**
1. Navigate to **Invoices** → **Create Invoice**
2. Fill in invoice details
3. **Upload 2-3 files** in attachments field
4. Click "Save"

**What to Verify:**
- ✅ All files uploaded to S3
- ✅ Multiple files separated by comma in database

**Check Database:**
```sql
SELECT attachments FROM invoices ORDER BY id DESC LIMIT 1;
```

**Expected Result:**
```
https://bucket.s3.region.com/invoices/123_file1.pdf,https://bucket.s3.region.com/invoices/124_file2.pdf
```

---

### Test 3: View Invoice with Attachments

**Steps:**
1. Navigate to **Invoices** → **Invoice List**
2. Click on the invoice you just created
3. Look for attachments section

**What to Verify:**
- ✅ Attachments are displayed
- ✅ Can see file names or download links
- ✅ Click download link → file downloads successfully
- ✅ Downloaded file opens correctly

---

### Test 4: Edit Invoice - Add New Attachment

**Steps:**
1. Navigate to existing invoice
2. Click "Edit"
3. Add a new attachment file
4. Click "Update"

**What to Verify:**
- ✅ New file added to S3
- ✅ Database shows both old and new file URLs
- ✅ Both files accessible

---

### Test 5: Edit Invoice - Replace Attachment

**Steps:**
1. Edit an existing invoice
2. Remove old attachment
3. Add new attachment
4. Save

**What to Verify:**
- ✅ Old local file deleted (if it was local)
- ✅ New file uploaded to S3
- ✅ Database updated with new URL

---

### Test 6: Commission Invoice Upload

**Steps:**
1. Navigate to invoice commission section
2. Upload attachment for commission invoice
3. Save

**What to Verify:**
- ✅ File uploaded to S3 `invoices/` folder
- ✅ URL stored in database

---

### Test 7: Payment Invoice Upload

**Steps:**
1. Navigate to invoice payment section
2. Upload payment receipt/attachment
3. Save

**What to Verify:**
- ✅ File uploaded to S3
- ✅ Payment record saved correctly

---

## Phase 3: Test Checklist Files (10 minutes)

### Test 8: Upload New Checklist

**Steps:**
1. Login to admin panel
2. Navigate to **Upload Checklists** (route: `/admin/upload-checklists`)
3. Fill in checklist name
4. Upload a PDF or DOCX file
5. Click "Save"

**What to Verify:**
- ✅ Success message displayed
- ✅ No errors

**Check S3 Bucket:**
1. Go to AWS S3 Console → Your Bucket → `checklists/` folder
2. Look for file: `1234567890_checklist.pdf`
3. Verify file exists

**Check Database:**
```sql
SELECT id, name, file, created_at 
FROM upload_checklists 
ORDER BY id DESC 
LIMIT 1;
```

**Expected Result:**
- `file` column contains full S3 URL

---

### Test 9: View Uploaded Checklist

**Steps:**
1. Navigate to checklist list page
2. Find the checklist you uploaded
3. Click to view or download

**What to Verify:**
- ✅ File URL is correct
- ✅ File downloads successfully
- ✅ File opens correctly

---

## Phase 4: Edge Cases & Error Handling (10 minutes)

### Test 10: Upload File with Special Characters

**Steps:**
1. Rename a file to include special characters: `Test File @#$%&.pdf`
2. Upload in invoice or checklist
3. Save

**What to Verify:**
- ✅ File uploads successfully (special chars may be sanitized)
- ✅ File accessible from S3

---

### Test 11: Upload Large File

**Steps:**
1. Try uploading a 10MB+ file
2. Save

**What to Verify:**
- ✅ Upload completes (may take time)
- ✅ OR appropriate error if too large

**Note:** Check PHP upload limits:
```php
// In php.ini
upload_max_filesize = 20M
post_max_size = 25M
```

---

### Test 12: Save Without File (Optional Field)

**Steps:**
1. Create invoice WITHOUT attachment
2. Save

**What to Verify:**
- ✅ Invoice saves successfully
- ✅ No errors about missing file

---

### Test 13: Edit Without Changing File

**Steps:**
1. Edit existing invoice
2. Don't upload new file
3. Change other fields only
4. Save

**What to Verify:**
- ✅ Old file URL remains unchanged
- ✅ Other changes saved correctly

---

## Phase 5: Backward Compatibility (5 minutes)

### Test 14: View Old Invoice with Local Files

**If you have old invoices with local file references:**

**Steps:**
1. Find an old invoice (before migration)
2. View the invoice
3. Check attachments

**What to Verify:**
- ✅ Old local files still display (if they exist)
- ✅ No errors for missing files
- ✅ Can download old files if they exist in `/public/img/invoice/`

---

## Phase 6: Browser Testing (5 minutes)

### Test 15: Different Browsers

Test invoice upload in:
- ✅ Chrome
- ✅ Firefox  
- ✅ Safari (if available)
- ✅ Edge

---

## Phase 7: Verify S3 Bucket (5 minutes)

### Direct S3 Check

1. Login to AWS Console
2. Navigate to S3 Service
3. Open your bucket
4. Check folders:

```
your-bucket/
├── invoices/
│   ├── 1736000001_invoice1.pdf
│   ├── 1736000002_invoice2.pdf
│   └── ...
└── checklists/
    ├── 1736000003_checklist1.pdf
    └── ...
```

5. Click on a file
6. Verify:
   - ✅ File size is correct
   - ✅ Can download file from S3 console
   - ✅ File permissions allow public read (or signed URLs work)

---

## Common Issues & Solutions

### Issue 1: "Class 'Storage' not found"
**Solution:** Import was added, clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue 2: "S3 credentials not configured"
**Solution:** Check `.env` file has all AWS variables, then:
```bash
php artisan config:cache
```

### Issue 3: "Access Denied" error
**Solution:** Check S3 bucket permissions and IAM user permissions

### Issue 4: Files upload but can't download
**Solution:** 
- Make bucket public OR
- Use signed URLs OR  
- Set CORS policy on bucket

### Issue 5: "Maximum execution time exceeded"
**Solution:** Increase PHP timeout for large files:
```php
// In php.ini
max_execution_time = 300
```

---

## Success Criteria

✅ **All invoice upload methods work (4 methods)**  
✅ **Checklist upload works**  
✅ **Files appear in S3 bucket**  
✅ **Database stores full S3 URLs**  
✅ **Files can be downloaded**  
✅ **No errors in browser console**  
✅ **No errors in Laravel logs**  

---

## Rollback Procedure (If Tests Fail)

If critical issues found:

```bash
# Rollback code changes
git checkout HEAD~1 app/Http/Controllers/Admin/InvoiceController.php
git checkout HEAD~1 app/Http/Controllers/Admin/UploadChecklistController.php

# Clear cache
php artisan config:clear
php artisan cache:clear
```

---

## Test Results Template

Copy and fill this out:

```
## Test Results - S3 Migration

Date: _____________
Tester: _____________

### Invoice Attachments
- [ ] Test 1: Single attachment - PASS/FAIL
- [ ] Test 2: Multiple attachments - PASS/FAIL  
- [ ] Test 3: View attachments - PASS/FAIL
- [ ] Test 4: Add attachment - PASS/FAIL
- [ ] Test 5: Replace attachment - PASS/FAIL
- [ ] Test 6: Commission invoice - PASS/FAIL
- [ ] Test 7: Payment invoice - PASS/FAIL

### Checklist Files
- [ ] Test 8: Upload checklist - PASS/FAIL
- [ ] Test 9: View checklist - PASS/FAIL

### Edge Cases
- [ ] Test 10: Special characters - PASS/FAIL
- [ ] Test 11: Large file - PASS/FAIL
- [ ] Test 12: Without file - PASS/FAIL
- [ ] Test 13: Edit without file - PASS/FAIL
- [ ] Test 14: Backward compatibility - PASS/FAIL

### S3 Verification
- [ ] Files in S3 bucket - YES/NO
- [ ] Correct folder structure - YES/NO
- [ ] Files downloadable from S3 - YES/NO

### Issues Found:
_______________________________________
_______________________________________

### Overall Result: PASS / FAIL
```

---

## Quick Command Reference

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test S3 connection
php artisan tinker
>>> Storage::disk('s3')->exists('test.txt')

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check PHP upload limits
php -i | grep upload

# Check database
mysql -u root -p bansalcrm
> SELECT * FROM invoices ORDER BY id DESC LIMIT 1\G
```

---

Good luck with testing! 🚀

