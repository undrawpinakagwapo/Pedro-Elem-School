# 🎓 Pedro Elementary School Management System

A comprehensive web-based school management system built with PHP, MySQL, and modern web technologies.

---

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser

### Installation Steps

#### 1. Start XAMPP
```bash
# Linux
sudo /opt/lampp/lampp start

# Windows
Run XAMPP Control Panel and start Apache & MySQL
```

#### 2. Setup Application
```bash
# Linux - Create symlink
sudo ln -sf /path/to/Pedro-Elem-School/core /opt/lampp/htdocs/pedro-elem-school

# Windows - Copy files
Copy the 'core' folder to: C:\xampp\htdocs\pedro-elem-school
```

#### 3. Import Database
1. Open: http://localhost/phpmyadmin
2. Create database: `db_elementary_school_pedro`
3. Import: `db/db_elementary_school_pedro (11).sql`

#### 4. Access Application
- URL: http://localhost/pedro-elem-school
- Username: `admin`
- Password: `admin123`

---

## 🌐 Access from Other Devices

### Linux:
1. Edit: `/opt/lampp/etc/extra/httpd-xampp.conf`
2. Change all `Require local` to `Require all granted`
3. Restart Apache: `sudo /opt/lampp/lampp restart`
4. Open firewall: `sudo ufw allow 80/tcp`

### Windows:
1. Edit: `C:\xampp\apache\conf\extra\httpd-xampp.conf`
2. Change all `Require local` to `Require all granted`
3. Restart Apache in XAMPP Control Panel
4. Open Windows Firewall for port 80

### Find Your IP:
```bash
# Linux
hostname -I

# Windows
ipconfig
```

Then access from other devices: `http://YOUR-IP/pedro-elem-school`

---

## ✨ Features

- 👨‍🎓 Student Management
- 👨‍🏫 Faculty Management
- 📚 Curriculum Management
- 📊 Grade Entry & Reports
- 📅 Attendance Tracking
- 📢 Announcements
- 📱 QR Code Generation
- 📄 School Forms (SF9, SF10, SF2)
- 📥 Excel Import/Export

---

## 🔧 Configuration

Edit `core/config.env`:
```env
DBHOST=localhost
DBUSER=root
DBPWD=
DBNAME=db_elementary_school_pedro
BASE_PATH=/pedro-elem-school
```

---

## 🐛 Troubleshooting

### Can't access the application?
- Make sure Apache and MySQL are running in XAMPP
- Verify the path: http://localhost/pedro-elem-school

### Database connection error?
- Check MySQL is running
- Verify database was imported
- Check credentials in `core/config.env`

### 404 errors on pages?
- Check if `core/.htaccess` file exists
- Verify Apache mod_rewrite is enabled

---

## 📁 Project Structure

```
Pedro-Elem-School/
├── core/                    # Main application
│   ├── index.php           # Entry point
│   ├── config.env          # Configuration
│   ├── components/         # Controllers
│   ├── models/             # Database models
│   ├── views/              # Templates
│   └── public/             # Assets
└── db/                     # Database schemas
```

---

## 🔐 Default Login

```
Username: admin
Password: admin123
```

**⚠️ Change password after first login!**

---

## 💻 Technology Stack

- PHP 8.2+
- MySQL/MariaDB
- Apache
- FastRoute
- PHPMailer
- PhpSpreadsheet

---

## 📱 Cross-Platform

✅ Linux (Ubuntu, Debian, etc.)
✅ Windows (10, 11)
✅ macOS (with XAMPP)

---

## 🤝 Contributing

Contributions welcome! Please follow coding standards and test thoroughly.

---

## 📄 License

Educational use license.

---

**Ready to manage your school!** 🎓✨
