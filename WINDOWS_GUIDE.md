# 🪟 Complete Windows Setup Guide

## Pedro Elementary School Management System - Windows Installation

Everything you need to run this application on Windows!

---

## 📋 Table of Contents

1. [Requirements](#requirements)
2. [Quick Start](#quick-start)
3. [Detailed Installation](#detailed-installation)
4. [Network Access Setup](#network-access-setup)
5. [Troubleshooting](#troubleshooting)
6. [Configuration](#configuration)
7. [Maintenance](#maintenance)

---

## 🎯 Requirements

### Minimum:
- Windows 10 or 11
- 2GB RAM
- 500MB free disk space
- Internet connection (for initial setup)

### Software Needed:
- **XAMPP for Windows** (includes Apache, MySQL, PHP)
  - Download: https://www.apachefriends.org/

---

## 🚀 Quick Start

### Step 1: Install XAMPP

1. Download XAMPP for Windows from https://www.apachefriends.org/
2. Run the installer (`xampp-windows-x64-8.2.12-0-VS16-installer.exe`)
3. Install to `C:\xampp` (recommended default location)
4. Open XAMPP Control Panel
5. Click "Start" for both **Apache** and **MySQL**

### Step 2: Copy Application Files

**Option A: Direct Copy**
```cmd
xcopy "Pedro-Elem-School\core" "C:\xampp\htdocs\pedro-elem-school\" /E /I
```

**Option B: Manual Copy**
1. Open File Explorer
2. Navigate to your `Pedro-Elem-School` folder
3. Copy the entire `core` folder
4. Paste into `C:\xampp\htdocs\`
5. Rename to `pedro-elem-school`

### Step 3: Setup Database

1. Open browser and go to: http://localhost/phpmyadmin
2. Click "New" in left sidebar
3. Database name: `db_elementary_school_pedro`
4. Click "Create"
5. Click "Import" tab
6. Choose file: `Pedro-Elem-School\db\db_elementary_school_pedro (11).sql`
7. Click "Go" at the bottom
8. Wait for import to complete

### Step 4: Access Application

1. Open browser
2. Go to: **http://localhost/pedro-elem-school**
3. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

🎉 **Done! You're ready to use the system!**

---

## 📚 Detailed Installation

### Installing XAMPP

#### 1. Download
- Visit: https://www.apachefriends.org/download.html
- Select "XAMPP for Windows"
- Choose the latest version (8.2.x recommended)
- Download size: ~150MB

#### 2. Run Installer
- Double-click the downloaded file
- Click "Yes" if prompted by User Account Control
- If Windows Defender or antivirus warns you, click "Allow"

#### 3. Installation Options
- **Components to Install:** (Keep defaults)
  - ✅ Apache
  - ✅ MySQL
  - ✅ PHP
  - ✅ phpMyAdmin
  - ❌ FileZilla (optional, not needed)
  - ❌ Mercury (optional, not needed)
  - ❌ Tomcat (optional, not needed)

- **Installation Directory:** `C:\xampp`
- **Language:** English
- Click "Next" through all screens
- Click "Finish" when done

#### 4. First Launch
- XAMPP Control Panel should open automatically
- If not, run `C:\xampp\xampp-control.exe`
- **Important:** Run as Administrator (right-click → Run as administrator)

### Setting Up the Application

#### 1. File Structure
After copying, you should have:
```
C:\xampp\htdocs\pedro-elem-school\
├── index.php
├── config.env
├── .htaccess
├── components/
├── models/
├── views/
├── public/
├── vendor/
└── ...
```

#### 2. Configuration File
The file `C:\xampp\htdocs\pedro-elem-school\config.env` should contain:

```env
# Database Configuration
DBHOST=localhost
DBUSER=root
DBPWD=
DBNAME=db_elementary_school_pedro

# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/pedro-elem-school
APP_NAME="Pedro Elementary School"
URL_HOST=/pedro-elem-school/
BASE_PATH=/pedro-elem-school
```

**Note:** Default XAMPP MySQL has:
- Username: `root`
- Password: (empty)

#### 3. Database Import Options

**Method 1: phpMyAdmin (Recommended)**
1. Open: http://localhost/phpmyadmin
2. Create database
3. Import SQL file
4. Verify tables created

**Method 2: Command Line**
```cmd
cd C:\xampp\mysql\bin
mysql -u root db_elementary_school_pedro < "path\to\db_elementary_school_pedro (11).sql"
```

#### 4. Verify Installation
Test each URL:
- Main app: http://localhost/pedro-elem-school
- phpMyAdmin: http://localhost/phpmyadmin
- Dashboard: http://localhost/pedro-elem-school/xampp

All should load without errors.

---

## 🌐 Network Access Setup

Enable access from other devices (phones, tablets, other computers) on your network!

### Automated Setup (Easy Way)

1. **Run the Batch Script:**
   - Right-click `windows_enable_network_access.bat`
   - Select **"Run as administrator"**
   - Follow the prompts

2. **Restart Apache:**
   - Open XAMPP Control Panel
   - Click "Stop" for Apache
   - Click "Start" for Apache

3. **Done!** Access from other devices using the IP shown

### Manual Setup

#### Step 1: Edit XAMPP Configuration

1. **Open Config File:**
   - Navigate to: `C:\xampp\apache\conf\extra\`
   - Open `httpd-xampp.conf` in Notepad++ or similar

2. **Find and Replace:**
   - Search for: `Require local`
   - Replace with: `Require all granted`
   - Do this for **ALL occurrences** (usually 3-4 places)

3. **Save and Close**

#### Step 2: Configure Windows Firewall

**Option A: Using GUI**
1. Open Windows Security → Firewall & network protection
2. Click "Advanced settings"
3. Click "Inbound Rules" → "New Rule"
4. Select "Port" → Next
5. TCP and port "80" → Next
6. "Allow the connection" → Next
7. Check all profiles → Next
8. Name: "XAMPP Apache HTTP" → Finish

**Option B: Using PowerShell (Run as Administrator)**
```powershell
New-NetFirewallRule -DisplayName "XAMPP Apache HTTP" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow
```

**Option C: Using Command Prompt (Run as Administrator)**
```cmd
netsh advfirewall firewall add rule name="XAMPP Apache HTTP" dir=in action=allow protocol=TCP localport=80
```

#### Step 3: Find Your IP Address

**Method 1: Command Prompt**
```cmd
ipconfig
```
Look for "IPv4 Address" under your active network adapter.
Example: `192.168.1.100`

**Method 2: GUI**
1. Open Settings → Network & Internet
2. Click your connection (Wi-Fi or Ethernet)
3. Scroll down to "Properties"
4. Find "IPv4 address"

#### Step 4: Access from Other Devices

On your phone/tablet/other computer:
1. Connect to the **same WiFi network**
2. Open web browser
3. Go to: `http://YOUR-IP/pedro-elem-school`
4. Example: `http://192.168.1.100/pedro-elem-school`
5. Login with admin credentials

### Network Access URLs

| Device | URL |
|--------|-----|
| **This Computer** | `http://localhost/pedro-elem-school` |
| **Other Devices** | `http://YOUR-IP/pedro-elem-school` |
| **Example** | `http://192.168.1.100/pedro-elem-school` |

---

## 🔧 Troubleshooting

### Port 80 Already in Use

**Symptoms:**
- Apache won't start in XAMPP
- Error: "Port 80 in use by another application"

**Cause:** Windows services (IIS, SQL Server, Skype) using port 80

**Solution 1: Stop Conflicting Services**
```cmd
net stop http
net stop w3svc
```

**Solution 2: Change Apache Port**
1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "httpd.conf"
4. Find: `Listen 80`
5. Change to: `Listen 8080`
6. Save and restart Apache
7. Access via: `http://localhost:8080/pedro-elem-school`

### MySQL Won't Start

**Symptoms:**
- MySQL button stays red
- Error about port 3306

**Solution:**
```cmd
net stop mysql
```
Then start MySQL in XAMPP

### Permission Denied Errors

**Solution:**
- Right-click XAMPP Control Panel
- Select "Run as administrator"
- Start services

### Can't Access from Phone/Tablet

**Checklist:**
1. ✅ Is Apache running in XAMPP?
2. ✅ Did you restart Apache after config changes?
3. ✅ Is Windows Firewall allowing port 80?
4. ✅ Are both devices on the same WiFi?
5. ✅ Did you use the correct IP address?
6. ✅ Is your network profile set to "Private" not "Public"?

**Check Network Profile:**
1. Settings → Network & Internet
2. Click your connection
3. Network profile should be "Private"
4. If "Public", change to "Private"

### 404 Not Found on Links

**Cause:** Apache mod_rewrite not working

**Solution:**
1. Open: `C:\xampp\apache\conf\httpd.conf`
2. Find: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment
4. Find all: `AllowOverride None`
5. Change to: `AllowOverride All`
6. Save and restart Apache

### Database Connection Failed

**Checklist:**
1. Is MySQL running in XAMPP? (green status)
2. Check `config.env` credentials:
   - DBHOST=localhost
   - DBUSER=root
   - DBPWD= (empty)
3. Does database exist? Check phpMyAdmin
4. Was SQL file imported completely?

**Test Connection:**
```cmd
C:\xampp\mysql\bin\mysql -u root -e "SHOW DATABASES;"
```

### Pages Load Slowly

**Optimization:**

1. **Disable Windows Defender for XAMPP:**
   - Windows Security → Virus & threat protection
   - Manage settings → Add exclusion
   - Folder → Select `C:\xampp`

2. **Increase PHP Memory:**
   - Edit: `C:\xampp\php\php.ini`
   - Find: `memory_limit = 128M`
   - Change to: `memory_limit = 256M`
   - Restart Apache

3. **Enable OPcache:**
   - Edit: `C:\xampp\php\php.ini`
   - Find: `;opcache.enable=0`
   - Change to: `opcache.enable=1`
   - Restart Apache

---

## ⚙️ Configuration

### File Locations

| Item | Path |
|------|------|
| Application | `C:\xampp\htdocs\pedro-elem-school\` |
| Configuration | `C:\xampp\htdocs\pedro-elem-school\config.env` |
| Apache Config | `C:\xampp\apache\conf\httpd.conf` |
| XAMPP Config | `C:\xampp\apache\conf\extra\httpd-xampp.conf` |
| PHP Config | `C:\xampp\php\php.ini` |
| MySQL Config | `C:\xampp\mysql\bin\my.ini` |
| Database | `C:\xampp\mysql\data\` |
| Logs | `C:\xampp\apache\logs\` |

### Auto-Start XAMPP

**Option 1: XAMPP Control Panel**
1. Open XAMPP Control Panel
2. Click "Config" (top right)
3. Check "Apache" and "MySQL" under "Autostart modules"

**Option 2: Install as Windows Service**
1. Open XAMPP Control Panel as Administrator
2. Click red "X" next to Apache → "Install as service"
3. Click red "X" next to MySQL → "Install as service"
4. Services will now start automatically with Windows

### Desktop Shortcuts

**Create Shortcut to Application:**
1. Right-click Desktop → New → Shortcut
2. Location: `http://localhost/pedro-elem-school`
3. Name: "Pedro Elementary School"

**Create Shortcut to XAMPP:**
1. Navigate to: `C:\xampp\`
2. Right-click `xampp-control.exe`
3. Send to → Desktop (create shortcut)

---

## 🔄 Maintenance

### Backup Database

**Using phpMyAdmin:**
1. Open: http://localhost/phpmyadmin
2. Click database: `db_elementary_school_pedro`
3. Click "Export" tab
4. Click "Go"
5. Save the `.sql` file

**Using Command Line:**
```cmd
cd C:\xampp\mysql\bin
mysqldump -u root db_elementary_school_pedro > backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%.sql
```

### Restore Database

**Using phpMyAdmin:**
1. Open: http://localhost/phpmyadmin
2. Click database
3. Click "Import" tab
4. Choose backup file
5. Click "Go"

**Using Command Line:**
```cmd
cd C:\xampp\mysql\bin
mysql -u root db_elementary_school_pedro < backup.sql
```

### Update Application

1. Backup database
2. Backup `config.env` file
3. Copy new application files
4. Restore `config.env`
5. Clear browser cache
6. Test thoroughly

### Check Logs

**Apache Error Log:**
```
C:\xampp\apache\logs\error.log
```

**PHP Error Log:**
```
C:\xampp\php\logs\php_error_log
```

**MySQL Error Log:**
```
C:\xampp\mysql\data\mysql_error.log
```

### Restart Services

**Quick Restart:**
1. Open XAMPP Control Panel
2. Click "Stop" for Apache
3. Click "Stop" for MySQL
4. Wait 2 seconds
5. Click "Start" for MySQL (start first!)
6. Click "Start" for Apache

**Full Restart:**
```cmd
C:\xampp\xampp_restart.exe
```

---

## 💡 Tips & Best Practices

### Security

1. **Change Default Password:**
   - Login as admin
   - Change password immediately

2. **Secure MySQL:**
   ```cmd
   cd C:\xampp\mysql\bin
   mysql -u root
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'your_secure_password';
   ```

3. **Disable Directory Browsing:**
   - Already configured in `.htaccess`

4. **Use HTTPS (Optional):**
   - Enable SSL in XAMPP
   - Configure Apache for HTTPS

### Performance

1. Keep Windows updated
2. Regularly clear browser cache
3. Monitor disk space
4. Keep backups current

### Mobile Access

1. Create QR code with URL
2. Bookmark on mobile home screen
3. Use responsive features
4. Test on different devices

---

## 📊 Quick Reference

### Default Credentials

| Service | Username | Password | URL |
|---------|----------|----------|-----|
| Application | admin | admin123 | http://localhost/pedro-elem-school |
| phpMyAdmin | root | (empty) | http://localhost/phpmyadmin |
| MySQL | root | (empty) | localhost:3306 |

### Common Ports

| Service | Port |
|---------|------|
| Apache/HTTP | 80 |
| Apache/HTTPS | 443 |
| MySQL | 3306 |

### Useful Commands

```cmd
REM Check Apache status
netstat -ano | findstr :80

REM Check MySQL status  
netstat -ano | findstr :3306

REM Find IP address
ipconfig

REM Test database connection
C:\xampp\mysql\bin\mysql -u root -e "SELECT 1"

REM View PHP info
echo <?php phpinfo(); ?> > C:\xampp\htdocs\info.php
REM Then visit: http://localhost/info.php
```

---

## 🎉 You're All Set!

Your Pedro Elementary School Management System is now running on Windows!

### Next Steps:
1. ✅ Login to the application
2. ✅ Change default password
3. ✅ Explore the features
4. ✅ Add your school data
5. ✅ Enable network access for mobile devices

### Need Help?
- Check [Troubleshooting](#troubleshooting) section
- Review [Configuration](#configuration) options
- See main [README.md](README.md) for features

---

**Enjoy managing your school!** 🎓✨

**Installation Date:** ___________  
**Your IP Address:** ___________  
**Status:** ✅ Working
