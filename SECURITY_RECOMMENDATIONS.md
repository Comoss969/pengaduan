# Security Recommendations - File Upload Feature

## Current Implementation Issues

### 1. **File Upload Security Vulnerabilities**

#### Issue: Original Filename Usage
```php
// Current code (INSECURE):
$foto = $target_dir . basename($_FILES['foto']['name']);
```

**Problems:**
- Files can be overwritten if same filename is uploaded
- No validation of file extension
- Potential directory traversal attacks
- No MIME type verification

#### Issue: No File Size Validation
```php
// Current code has no size check
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    // Directly uploads without size validation
}
```

**Problems:**
- Users can upload extremely large files
- Can cause disk space issues
- Can cause memory exhaustion

#### Issue: No Server-Side File Type Validation
```php
// Only browser-side validation exists:
<input type="file" name="foto" accept="image/*">
```

**Problems:**
- Browser validation can be bypassed
- Malicious files can be uploaded
- No MIME type checking on server

---

## Recommended Improvements

### 1. Secure File Upload Handler

Create a new file: `includes/secure_upload.php`

```php
<?php
/**
 * Secure File Upload Handler
 */

class SecureFileUpload {
    private $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $max_size = 5242880; // 5MB in bytes
    private $upload_dir = 'uploads/';
    
    public function upload($file) {
        $result = [
            'success' => false,
            'message' => '',
            'filepath' => null
        ];
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $result['message'] = 'No file uploaded or upload error occurred.';
            return $result;
        }
        
        // Validate file size
        if ($file['size'] > $this->max_size) {
            $result['message'] = 'File too large. Maximum size is 5MB.';
            return $result;
        }
        
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $this->allowed_types)) {
            $result['message'] = 'Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.';
            return $result;
        }
        
        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_extensions)) {
            $result['message'] = 'Invalid file extension.';
            return $result;
        }
        
        // Generate unique filename
        $unique_name = md5(uniqid(rand(), true)) . '.' . $extension;
        $target_path = $this->upload_dir . $unique_name;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Set proper permissions
            chmod($target_path, 0644);
            
            $result['success'] = true;
            $result['message'] = 'File uploaded successfully.';
            $result['filepath'] = $target_path;
        } else {
            $result['message'] = 'Failed to move uploaded file.';
        }
        
        return $result;
    }
    
    public function deleteFile($filepath) {
        if (file_exists($filepath) && strpos($filepath, $this->upload_dir) === 0) {
            return unlink($filepath);
        }
        return false;
    }
}
?>
```

### 2. Updated Usage in admin_dashboard.php

```php
// Replace current file upload code with:
require_once 'includes/secure_upload.php';

$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $uploader = new SecureFileUpload();
    $upload_result = $uploader->upload($_FILES['foto']);
    
    if ($upload_result['success']) {
        $foto = $upload_result['filepath'];
    } else {
        $error = $upload_result['message'];
        // Don't proceed with comment submission if file upload failed
    }
}

// Only insert comment if no upload errors
if (!isset($error)) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, komentar, foto, is_admin) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $komentar, $foto, true]);
}
```

### 3. PHP Configuration Recommendations

Add to `.htaccess` in uploads directory:

```apache
# Prevent PHP execution in uploads directory
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Only allow image files
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

Update `php.ini` settings:

```ini
; Maximum file upload size
upload_max_filesize = 5M
post_max_size = 6M

; Maximum number of files
max_file_uploads = 20

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

---

## Additional Security Measures

### 1. Content Security Policy (CSP)
Add to header.php:
```php
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self' 'unsafe-inline';");
```

### 2. File Upload Rate Limiting
Implement rate limiting to prevent abuse:
```php
// Track uploads per user per hour
$stmt = $pdo->prepare("
    SELECT COUNT(*) as upload_count 
    FROM comments 
    WHERE user_id = ? 
    AND foto IS NOT NULL 
    AND tanggal > DATE_SUB(NOW(), INTERVAL 1 HOUR)
");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch();

if ($result['upload_count'] >= 10) {
    $error = "Upload limit reached. Please try again later.";
}
```

### 3. Image Validation
Add image dimension validation:
```php
list($width, $height) = getimagesize($file['tmp_name']);

if ($width > 4000 || $height > 4000) {
    $error = "Image dimensions too large. Maximum 4000x4000 pixels.";
}
```

### 4. Virus Scanning (Optional)
If ClamAV is available:
```php
function scanFile($filepath) {
    $output = shell_exec("clamscan " . escapeshellarg($filepath));
    return strpos($output, 'OK') !== false;
}
```

---

## Testing Security Improvements

### Test Cases:
1. ✓ Upload valid image (JPG, PNG, GIF)
2. ✓ Upload oversized image (>5MB)
3. ✓ Upload non-image file (PDF, EXE, PHP)
4. ✓ Upload file with double extension (image.php.jpg)
5. ✓ Upload file with special characters in name
6. ✓ Attempt directory traversal (../../etc/passwd)
7. ✓ Upload same file twice (should get unique names)
8. ✓ Verify uploaded files have correct permissions
9. ✓ Verify PHP files cannot execute in uploads directory
10. ✓ Test rate limiting

---

## Priority Recommendations

### High Priority (Implement Immediately):
1. ✅ Add server-side file type validation
2. ✅ Add file size validation
3. ✅ Generate unique filenames (prevent overwrites)
4. ✅ Validate MIME types

### Medium Priority (Implement Soon):
1. ⏳ Add .htaccess to uploads directory
2. ⏳ Implement rate limiting
3. ⏳ Add image dimension validation
4. ⏳ Add CSP headers

### Low Priority (Nice to Have):
1. ⏳ Implement virus scanning
2. ⏳ Add image optimization/compression
3. ⏳ Implement CDN for file storage
4. ⏳ Add watermarking for uploaded images

---

## Current Status

**Security Level: ⚠️ MODERATE RISK**

The current implementation works but has security vulnerabilities that should be addressed before production deployment.

**Recommendation:** Implement the secure file upload handler before allowing public access to the application.
