# Quick Testing Checklist - S3 Migration

## Before You Start (2 minutes)

1. **Verify .env has AWS credentials:**
   ```bash
   # Check these exist in .env file:
   AWS_ACCESS_KEY_ID=xxx
   AWS_SECRET_ACCESS_KEY=xxx
   AWS_DEFAULT_REGION=xxx
   AWS_BUCKET=xxx
   AWS_URL=xxx
   ```

2. **Test S3 connection:**
   ```bash
   php artisan tinker
   >>> Storage::disk('s3')->put('test.txt', 'Hello');
   >>> exit
   ```

---

## Invoice Tests (10 minutes)

### Quick Test Flow:

**Test 1: Create Invoice with Attachment**
```
1. Admin → Invoices → Create
2. Fill details + Upload PDF
3. Save
4. ✓ Success message?
5. ✓ Check S3 bucket → invoices/ folder
6. ✓ File there?
```

**Test 2: View Invoice**
```
1. Open the invoice you created
2. ✓ Attachment shows?
3. ✓ Can download?
```

**Test 3: Edit Invoice**
```
1. Edit invoice → Add another file
2. Save
3. ✓ Both files in S3?
```

---

## Checklist Tests (5 minutes)

**Test 4: Upload Checklist**
```
1. Admin → Upload Checklists
2. Name: "Test Checklist"
3. Upload PDF/DOCX
4. Save
5. ✓ Success message?
6. ✓ Check S3 bucket → checklists/ folder
7. ✓ File there?
```

---

## Verify in AWS (3 minutes)

**Login to AWS S3 Console:**
```
1. Open your bucket
2. Check folders exist:
   - invoices/
   - checklists/
3. ✓ Files inside?
4. ✓ Click file → Can download?
```

---

## Check Database (2 minutes)

**Run in phpMyAdmin or MySQL:**
```sql
-- Check latest invoice
SELECT id, attachments FROM invoices ORDER BY id DESC LIMIT 1;

-- Expected: Full S3 URL like:
-- https://bucket.s3.region.amazonaws.com/invoices/123456_file.pdf

-- Check latest checklist  
SELECT id, name, file FROM upload_checklists ORDER BY id DESC LIMIT 1;

-- Expected: Full S3 URL
```

---

## Success = All ✓ Green

- ✅ Files upload without errors
- ✅ Files appear in S3 bucket
- ✅ Database has full S3 URLs
- ✅ Files can be downloaded
- ✅ No errors in browser console

---

## If Something Fails

**Check Laravel logs:**
```bash
tail -50 storage/logs/laravel.log
```

**Clear caches:**
```bash
php artisan config:clear
php artisan cache:clear
```

**Verify AWS credentials:**
- Check .env file
- Test S3 connection in tinker

---

## Time Estimate

- Setup check: 2 min
- Invoice tests: 10 min
- Checklist test: 5 min
- S3 verification: 3 min
- Database check: 2 min

**Total: ~20 minutes**

---

## Report Results

After testing, report:
- ✅ PASS - All tests successful
- ⚠️ PARTIAL - Some issues found
- ❌ FAIL - Critical errors

Include:
- What worked
- What failed
- Error messages (if any)
- Screenshots (if helpful)

