# S3 Migration - Quick Reference

## What Changed?

### Invoice Attachments
**Before:** Saved to `public/img/invoice/filename.pdf`  
**After:** Saved to S3 as full URL `https://bucket.s3.region.amazonaws.com/invoices/timestamp_filename.pdf`

### Checklist Files
**Before:** Saved to `public/checklists/filename.pdf`  
**After:** Saved to S3 as full URL `https://bucket.s3.region.amazonaws.com/checklists/timestamp_filename.pdf`

---

## Files Modified

1. `app/Http/Controllers/Admin/InvoiceController.php`
   - Added: `use Illuminate\Support\Facades\Storage;`
   - Updated methods: `store()`, `updatecominvoices()`, `generalStore()`, `updategeninvoices()`

2. `app/Http/Controllers/Admin/UploadChecklistController.php`
   - Added: `use Illuminate\Support\Facades\Storage;`
   - Updated method: `store()`

---

## S3 Upload Pattern Used

```php
$fileName = time() . '_' . $file->getClientOriginalName();
$filePath = 'folder/' . $fileName;
Storage::disk('s3')->put($filePath, file_get_contents($file));
$fileUrl = Storage::disk('s3')->url($filePath);
// Store $fileUrl in database
```

---

## Testing Commands

```bash
# Check if S3 is configured
php artisan tinker
>>> Storage::disk('s3')->exists('test.txt')

# Test file upload
>>> Storage::disk('s3')->put('test.txt', 'Hello World');
>>> Storage::disk('s3')->url('test.txt');
```

---

## Required .env Variables

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=your_region
AWS_BUCKET=your_bucket
AWS_URL=https://your_bucket.s3.region.amazonaws.com
```

---

## What Stayed Local?

✅ Profile Images (`public/img/profile_imgs/`)  
✅ Service Images (`public/img/service_imgs/`)

---

## Deployment Checklist

- [ ] Verify AWS credentials in production .env
- [ ] Test S3 connection on production
- [ ] Deploy code changes
- [ ] Test invoice upload
- [ ] Test checklist upload
- [ ] Monitor S3 usage

---

## Rollback

If issues occur:
```bash
git revert <commit-hash>
```

Database doesn't need changes (backward compatible).

