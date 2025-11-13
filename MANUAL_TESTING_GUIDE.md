# Manual Testing Guide - Comments Foto Feature

## Automated Test Results
✅ **All automated tests PASSED (6/6)**

### Tests Completed:
1. ✓ Column 'foto' exists in comments table
2. ✓ INSERT statement preparation successful
3. ✓ INSERT with NULL foto value works
4. ✓ INSERT with foto path works
5. ✓ SELECT statement with foto column works
6. ✓ Existing comments integrity maintained
7. ✓ Uploads directory exists and is writable

---

## Manual Testing Checklist

### Critical Path Testing (Required)

#### 1. Admin Dashboard Access
- [ ] Navigate to: `http://localhost/pengaduan/admin_dashboard.php`
- [ ] **Expected:** Page loads without PDOException error
- [ ] **Expected:** All posts display correctly
- [ ] **Status:** ⏳ Pending manual verification

#### 2. Admin Comment Submission WITH Photo
- [ ] Click "Komentar sebagai Admin" button on any post
- [ ] Enter comment text: "Test comment with photo"
- [ ] Upload an image file (JPG, PNG, or GIF)
- [ ] Click "Kirim" button
- [ ] **Expected:** Success message appears
- [ ] **Expected:** Comment displays with photo thumbnail
- [ ] **Expected:** Photo is clickable/viewable
- [ ] **Status:** ⏳ Pending manual verification

#### 3. Admin Comment Submission WITHOUT Photo
- [ ] Click "Komentar sebagai Admin" button on any post
- [ ] Enter comment text: "Test comment without photo"
- [ ] Do NOT upload any file
- [ ] Click "Kirim" button
- [ ] **Expected:** Success message appears
- [ ] **Expected:** Comment displays without photo
- [ ] **Expected:** No errors or broken image icons
- [ ] **Status:** ⏳ Pending manual verification

#### 4. Comment Display Verification
- [ ] Verify admin comments show "[Admin] username" prefix
- [ ] Verify photos display with proper styling (max-width: 200px)
- [ ] Verify comments without photos display normally
- [ ] Verify timestamps display correctly
- [ ] **Status:** ⏳ Pending manual verification

---

### Thorough Testing (Comprehensive)

#### 5. User Dashboard Testing
- [ ] Navigate to: `http://localhost/pengaduan/user_dashboard.php`
- [ ] Login as a regular user
- [ ] **Expected:** Page loads without errors
- [ ] Test adding comment WITH photo
- [ ] Test adding comment WITHOUT photo
- [ ] Verify user comments display correctly
- [ ] **Status:** ⏳ Pending manual verification

#### 6. File Upload Validation
Test with various file types:
- [ ] **JPG file:** Should upload successfully
- [ ] **PNG file:** Should upload successfully
- [ ] **GIF file:** Should upload successfully
- [ ] **JPEG file:** Should upload successfully
- [ ] **Non-image file (PDF, TXT):** Should be rejected by browser (accept="image/*")
- [ ] **Status:** ⏳ Pending manual verification

#### 7. File Upload Edge Cases
- [ ] **Very large image (>5MB):** Check if upload succeeds or fails gracefully
- [ ] **Image with special characters in filename:** Verify proper handling
- [ ] **Multiple rapid submissions:** Check for race conditions
- [ ] **Empty file upload:** Verify NULL handling
- [ ] **Status:** ⏳ Pending manual verification

#### 8. Database Integrity
- [ ] Check phpMyAdmin: `http://localhost/phpmyadmin`
- [ ] Navigate to `pengaduan` database → `comments` table
- [ ] Verify `foto` column exists with type VARCHAR(255) NULL
- [ ] Check existing comments have NULL in foto column
- [ ] Check new comments with photos have proper file paths
- [ ] **Status:** ⏳ Pending manual verification

#### 9. Cross-Browser Testing
Test in multiple browsers:
- [ ] **Chrome/Edge:** Full functionality works
- [ ] **Firefox:** Full functionality works
- [ ] **Safari (if available):** Full functionality works
- [ ] **Status:** ⏳ Pending manual verification

#### 10. Security & Error Handling
- [ ] Test file upload without being logged in (should redirect to login)
- [ ] Test uploading extremely large files (should handle gracefully)
- [ ] Test SQL injection in comment text (should be prevented by prepared statements)
- [ ] Verify uploaded files are stored in `/uploads` directory
- [ ] Check file permissions on uploaded files
- [ ] **Status:** ⏳ Pending manual verification

---

## Known Issues & Limitations

### Current Implementation Notes:
1. **File Upload Security:** 
   - Files are uploaded with original filenames (potential security risk)
   - Consider implementing file hashing for unique filenames
   - No file size validation in PHP code

2. **File Type Validation:**
   - Only browser-side validation (accept="image/*")
   - Consider adding server-side MIME type checking

3. **Storage:**
   - Files stored in `/uploads` directory
   - No automatic cleanup of orphaned files
   - No file size limits enforced

### Recommendations for Future Improvements:
```php
// Example: Add server-side validation
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

if ($_FILES['foto']['size'] > $max_size) {
    $error = "File too large. Maximum size is 5MB.";
}

if (!in_array($_FILES['foto']['type'], $allowed_types)) {
    $error = "Invalid file type. Only JPG, PNG, and GIF allowed.";
}

// Generate unique filename
$foto = $target_dir . md5(uniqid()) . '.' . pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
```

---

## Quick Test Commands

### Check Database Schema:
```sql
SHOW COLUMNS FROM comments;
```

### View Recent Comments:
```sql
SELECT id, post_id, user_id, komentar, foto, is_admin, tanggal 
FROM comments 
ORDER BY tanggal DESC 
LIMIT 10;
```

### Count Comments with Photos:
```sql
SELECT COUNT(*) as comments_with_photos 
FROM comments 
WHERE foto IS NOT NULL;
```

---

## Troubleshooting

### If you encounter errors:

**Error: "Column 'foto' not found"**
- Solution: Run `php run_comments_migration.php` again

**Error: "Failed to upload file"**
- Check uploads directory exists: `c:/xampp/htdocs/pengaduan/uploads`
- Check directory permissions (should be writable)
- Check PHP upload settings in php.ini

**Error: "File too large"**
- Check php.ini settings:
  - `upload_max_filesize`
  - `post_max_size`
  - `memory_limit`

**Photos not displaying:**
- Check file path in database
- Verify file exists in uploads directory
- Check browser console for 404 errors

---

## Test Results Summary

### Automated Tests: ✅ 6/6 PASSED
### Manual Tests: ⏳ Pending

**Next Action:** Please perform the manual tests listed above and report any issues found.
