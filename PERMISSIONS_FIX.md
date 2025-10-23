# File Permissions Fix

## Issue Description
When adding or editing students, the application failed with a "Permission denied" error when trying to generate QR codes.

### Error Message
```
Fatal error: file_put_contents(/home/mono/Pedro-Elem-School/core/uploads/qrcodes/qr.log): 
Failed to open stream: Permission denied
```

## Root Cause
The `uploads` directory was owned by the user `mono`, but XAMPP's Apache server runs as the `daemon` user. This prevented Apache from writing QR code images and log files.

## Solution Applied

### 1. Changed Ownership
```bash
sudo chown -R daemon:daemon /home/mono/Pedro-Elem-School/core/uploads/
```
This changes the owner of the `uploads` directory and all its contents to the `daemon` user (Apache's user in XAMPP).

### 2. Set Proper Permissions
```bash
sudo chmod -R 755 /home/mono/Pedro-Elem-School/core/uploads/
```
This ensures:
- **Owner (daemon):** Read, Write, Execute (7)
- **Group (daemon):** Read, Execute (5)
- **Others:** Read, Execute (5)

## Verification

After the fix, the permissions look like:
```bash
drwxr-xr-x  4 daemon daemon  4096 uploads/
drwxr-xr-x  2 daemon daemon 12288 uploads/qrcodes/
drwxr-xr-x  2 daemon daemon  4096 uploads/users/
```

## Directories Affected

1. **`core/uploads/qrcodes/`**
   - Stores QR codes generated for student LRNs
   - Stores `qr.log` for debugging QR generation

2. **`core/uploads/users/`**
   - Stores user profile images
   - Used for faculty, staff, and student photos

## What Can Now Be Done

✅ Add new students (with automatic QR code generation)
✅ Edit student information
✅ Upload profile images for users
✅ Upload faculty photos
✅ Generate attendance QR codes
✅ Export reports with QR codes

## Important Notes

### For Development (XAMPP)
- Apache runs as `daemon` user in XAMPP
- All upload directories must be owned by `daemon` or have write permissions for `daemon`

### For Production
If deploying to a production server:
1. Check which user the web server runs as:
   ```bash
   ps aux | grep httpd  # or apache2, nginx
   ```

2. Change ownership accordingly:
   ```bash
   # For Apache (www-data is common on Ubuntu/Debian)
   sudo chown -R www-data:www-data /path/to/uploads/
   
   # For Nginx (nginx user)
   sudo chown -R nginx:nginx /path/to/uploads/
   ```

3. Set secure permissions:
   ```bash
   sudo chmod -R 755 /path/to/uploads/
   ```

### Security Best Practices

1. **Never use 777 permissions** - This makes files writable by everyone
2. **Keep uploads outside the public directory** - Already done (✅)
3. **Validate uploaded files** - Already implemented in controllers
4. **Limit file sizes** - Already configured in PHP settings

## Troubleshooting

### If permission errors persist:

1. **Check Apache user:**
   ```bash
   ps aux | grep httpd | grep -v grep
   ```

2. **Verify directory ownership:**
   ```bash
   ls -la /home/mono/Pedro-Elem-School/core/uploads/
   ```

3. **Check if SELinux is blocking:**
   ```bash
   # If on RHEL/CentOS/Fedora
   sudo setenforce 0  # Temporarily disable
   # Or set proper context
   sudo chcon -R -t httpd_sys_rw_content_t /path/to/uploads/
   ```

4. **Verify Apache has access to parent directories:**
   ```bash
   namei -l /home/mono/Pedro-Elem-School/core/uploads/qrcodes/
   ```

## Related Files

- QR Code generation: `core/components/StudentManagementController/AfterSubmitTrait.php`
- Image uploads: Various controller files
- QR Code library: `core/vendor/chillerlan/php-qrcode/`

## Testing

After applying the fix, test:
1. Add a new student → Should generate QR code ✅
2. Upload a profile image → Should save successfully ✅
3. View student details → QR code should be visible ✅

## Summary

**Problem:** Permission denied when writing to uploads directory
**Cause:** Directory owned by wrong user
**Fix:** Changed ownership to Apache's daemon user
**Result:** Application can now write QR codes and images successfully ✅

