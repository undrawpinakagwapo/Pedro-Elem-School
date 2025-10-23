# URL Configuration Guide

## Overview
All API endpoints and URLs in this application now use a single base URL configuration from the environment file (`core/config.env`). This allows you to change the base URL in one place without modifying any code files.

## Configuration File
**Location:** `/core/config.env`

```env
DBHOST=localhost
DBUSER=root
DBPWD=
DBNAME=db_elementary_school_pedro
BASE_PATH=/pedro-elem-school
APP_NAME="Pedro Elementary School"
```

## Key Configuration Variables

### BASE_PATH
- **Purpose:** The base path for your application when hosted on a web server
- **Default:** `/pedro-elem-school`
- **Examples:**
  - For root installation: `BASE_PATH=`
  - For subdirectory: `BASE_PATH=/my-app`
  - For XAMPP: `BASE_PATH=/pedro-elem-school`

### APP_NAME
- **Purpose:** The application name displayed in browser titles and headers
- **Default:** `"Pedro Elementary School"`
- **Note:** Must be quoted if it contains spaces

## How It Works

### In PHP Files
All URLs now use `$_ENV['BASE_PATH']`:

```php
// Links
<a href="<?=$_ENV['BASE_PATH']?>/component/dashboard/index">Dashboard</a>

// Form actions
<form action="<?=$_ENV['BASE_PATH']?>/auth" method="POST">

// Assets (CSS, JS, Images)
<link rel="stylesheet" href="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/css/style.css">
<script src="<?=$_ENV['BASE_PATH']?>/public/admin_template/assets/js/jquery/jquery.min.js"></script>
<img src="<?=$_ENV['BASE_PATH']?>/src/images/logos/logo.png">

// Redirects (using helper function)
redirect('/dashboard'); // Automatically includes BASE_PATH
```

### In JavaScript Files
The JavaScript gets the base URL from the HTML data attribute:

```javascript
// In main.js
const URL_BASED = document.querySelector('.URL_HOST').getAttribute('data-url');

// Then use it for API calls
main.modalOpen('Title', html, footer, '/component/user/save');
// Becomes: BASE_PATH + '/component/user/save'
```

## Files Updated

### Core Files
- `core/index.php` - Router BASE_PATH handling
- `core/libraries/Helper.php` - redirect() function
- `core/middleware/AuthMiddleware.php` - All redirects
- `core/view.php` - View loader

### Template Files
#### Backoffice (Admin Dashboard)
- `core/views/backoffice/header.php` - All CSS/JS links
- `core/views/backoffice/footer.php` - All JavaScript includes
- `core/views/backoffice/sidebar.php` - Logo and menu links
- `core/views/backoffice/top.php` - Logout link
- `core/views/backoffice/index.php` - Custom JS loader

#### Customer Template
- `core/views/customer_template/header.php` - All CSS/JS links
- `core/views/customer_template/footer.php` - All JavaScript includes
- `core/views/customer_template/top.php` - All navigation links
- `core/views/customer_template/loading.php` - Login/logout links
- `core/views/customer_template/index.php` - Custom JS loader

### Component Files
- `core/components/DashboardController/views/custom.php` - All dashboard links
- `core/components/UserController/UserController.php` - Email links
- `core/components/UserController/pages/login_form.php` - All form actions
- `core/components/UserController/pages/forgot_password.php` - Form action
- `core/components/UserController/pages/otp.php` - Form action
- `core/components/UserController/pages/changepassword.php` - Form action
- `core/components/UserManagementController/views/modal_details.php` - Image paths
- `core/components/StudentManagementController/views/modal_details.php` - Image paths
- `core/components/StudentProfileController/views/custom.php` - Image paths
- `core/components/MyProfileController/views/custom.php` - Image paths
- `core/components/FacultyManagementController/views/modal_details.php` - Image paths

### JavaScript Files
- `core/public/js/main.js` - Uses URL_BASED variable from data attribute

## Changing the URL

### Moving to a Different Directory
If you want to move from `/pedro-elem-school` to `/school-app`:

1. Edit `core/config.env`:
   ```env
   BASE_PATH=/school-app
   ```

2. Update symlink (Linux):
   ```bash
   sudo rm /opt/lampp/htdocs/pedro-elem-school
   sudo ln -sf /home/mono/Pedro-Elem-School/core /opt/lampp/htdocs/school-app
   ```

3. Access at: `http://localhost/school-app`

### Installing at Root
To install at the root of your domain:

1. Edit `core/config.env`:
   ```env
   BASE_PATH=
   ```

2. Configure web server to point to the `core` directory

3. Access at: `http://yourdomain.com`

### Production Deployment
For production on a different domain:

1. Edit `core/config.env`:
   ```env
   BASE_PATH=
   # Or if in subdirectory:
   BASE_PATH=/app
   ```

2. Update database credentials:
   ```env
   DBHOST=your-production-db-host
   DBUSER=your-db-user
   DBPWD=your-secure-password
   DBNAME=your-db-name
   ```

3. Deploy the `core` directory to your web server

## Benefits

✅ **Single Point of Configuration:** Change URL in one place
✅ **Environment Flexibility:** Easy to move between dev/staging/production
✅ **No Code Changes:** Never need to search/replace URLs in code
✅ **Portable:** Easy to clone and deploy to different environments
✅ **Clean Codebase:** Consistent URL handling throughout

## Troubleshooting

### Links Not Working
- Check that `BASE_PATH` is correctly set in `core/config.env`
- Ensure there are no trailing slashes in `BASE_PATH`
- Verify `.htaccess` file exists in `core/` directory

### Assets Not Loading (404 errors)
- Verify `BASE_PATH` matches your actual installation path
- Check Apache mod_rewrite is enabled
- Ensure symlink is correct (Linux) or files are in the right directory (Windows)

### Redirects Going to Wrong URL
- Check `getSegment()` function is stripping `BASE_PATH` correctly
- Verify all `redirect()` calls use the helper function (not direct `header()` calls)

## Notes

- The `BASE_PATH` should **not** have a trailing slash
- The `BASE_PATH` should start with `/` (except for root installation where it's empty)
- All image paths should include `BASE_PATH`
- All CSS/JS file paths should include `BASE_PATH`
- All internal links should include `BASE_PATH`
- External CDN links (like Bootstrap, jQuery from CDN) do not need `BASE_PATH`

## Summary

You now have a fully centralized URL configuration system. **To change where your application is hosted, you only need to update the `BASE_PATH` value in `core/config.env`.**

