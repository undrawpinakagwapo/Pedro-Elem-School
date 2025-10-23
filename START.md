# 🚀 Quick Start Guide

## To Start This Codebase:

### 1. **Start XAMPP**

**Linux:**
```bash
sudo /opt/lampp/lampp start
```

**Windows:**
- Run `C:\xampp\xampp-control.exe`
- Click "Start" for Apache
- Click "Start" for MySQL

---

### 2. **Setup Application**

**Linux:**
```bash
sudo ln -sf $(pwd)/core /opt/lampp/htdocs/pedro-elem-school
```

**Windows:**
- Copy the `core` folder to `C:\xampp\htdocs\`
- Rename it to `pedro-elem-school`

---

### 3. **Import Database**

1. Open browser: **http://localhost/phpmyadmin**
2. Click "New" to create database
3. Name: `db_elementary_school_pedro`
4. Click "Import" tab
5. Choose file: `db/db_elementary_school_pedro (11).sql`
6. Click "Go"

---

### 4. **Access Application**

Open browser and go to:
```
http://localhost/pedro-elem-school
```

**Login:**
- Username: `admin`
- Password: `admin123`

---

## ✅ That's It!

Your school management system is now running!

---

## 📱 Access from Phone/Tablet?

### Enable Network Access:

**Linux:**
```bash
# 1. Edit config
sudo nano /opt/lampp/etc/extra/httpd-xampp.conf
# Change all "Require local" to "Require all granted"

# 2. Restart Apache
sudo /opt/lampp/lampp restart

# 3. Open firewall
sudo ufw allow 80/tcp

# 4. Find your IP
hostname -I
```

**Windows:**
```
1. Edit: C:\xampp\apache\conf\extra\httpd-xampp.conf
2. Change all "Require local" to "Require all granted"
3. Restart Apache in XAMPP Control Panel
4. Open Windows Firewall for port 80
5. Run: ipconfig (to find your IP)
```

**Then access from other devices:**
```
http://YOUR-IP-ADDRESS/pedro-elem-school
```

---

## 🆘 Having Issues?

### Apache won't start?
- Check if port 80 is already in use
- Run XAMPP as Administrator (Windows)

### MySQL won't start?
- Check if port 3306 is already in use
- Stop any other MySQL services

### Can't see the login page?
- Make sure you're using: http://localhost/pedro-elem-school
- Check that Apache is running (green in XAMPP)

### Database connection error?
- Verify MySQL is running
- Check that database was imported
- Verify `core/config.env` has correct credentials

---

**Need more help? Check README.md for detailed instructions.**
