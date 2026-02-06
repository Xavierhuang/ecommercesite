# Quick Local Setup - Diversiply

## ✅ Config Files Created!

Your `config.php` and `admin/config.php` have been updated with local paths.

## 📋 Next Steps

### 1. Update Database Credentials

Edit both config files if your database credentials are different:

**config.php** (line 27-29):
```php
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');          // Change if needed
define('DB_PASSWORD', '');              // Add your password
```

**admin/config.php** (line 32-34):
```php
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');          // Change if needed
define('DB_PASSWORD', '');              // Add your password
```

### 2. Create Local Database

Open your MySQL client (Herd comes with DBngin or use TablePlus):

```sql
CREATE DATABASE diversiply_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import Production Database

**Option A: If you have access to production server**
```bash
# SSH to production
ssh user@dev.diversiply.co

# Export database
mysqldump -u dev_oc_4566 -p'dev_9b*E6=7%G{n3}' dev_oc_4566 > diversiply_export.sql

# Download the SQL file, then import locally:
mysql -u root -p diversiply_local < diversiply_export.sql
```

**Option B: Ask for database dump**
Request a database export from the client/server admin, then:
```bash
mysql -u root -p diversiply_local < received_dump.sql
```

### 4. Update Database URLs (After Import)

Run these SQL commands to update URLs to your local:

```sql
USE diversiply_local;

-- Update store URLs
UPDATE oc_setting 
SET value = 'http://diversiply.test/' 
WHERE `key` IN ('config_url', 'config_ssl');

UPDATE oc_setting 
SET value = 'http://diversiply.test/' 
WHERE `key` = 'config_url';

-- Check current URLs
SELECT * FROM oc_setting WHERE `key` LIKE '%url%';
```

### 5. Access Your Site

**Frontend:** http://diversiply.test  
**Admin Panel:** http://diversiply.test/admin

**Default Admin Credentials** (from production):
- Check with the client or check `oc_user` table

### 6. Clear Cache

```powershell
Remove-Item -Recurse -Force storage/cache/*
Remove-Item -Recurse -Force storage/modification/*
```

Or create necessary directories if they don't exist:
```powershell
New-Item -ItemType Directory -Path "storage/cache" -Force
New-Item -ItemType Directory -Path "storage/logs" -Force
New-Item -ItemType Directory -Path "storage/modification" -Force
New-Item -ItemType Directory -Path "storage/session" -Force
New-Item -ItemType Directory -Path "storage/upload" -Force
New-Item -ItemType Directory -Path "storage/download" -Force
```

## 🔧 Troubleshooting

### "Can't connect to database"
- Check MySQL is running (Herd → DBngin)
- Verify credentials in config.php
- Ensure database `diversiply_local` exists

### "Page not found" / 404 errors
- Clear cache: `Remove-Item -Recurse -Force storage/cache/*`
- Check .htaccess file exists in root

### "Permission denied" for storage/
```powershell
# On Windows, just ensure folders exist (created in step 6)
```

### Still getting path errors?
- Verify paths in config.php use forward slashes: `F:/PROJECTS/...`
- Restart Herd: Herd → Restart Services

## 🎉 You're Ready!

Once database is imported and URLs updated, visit:
- **Frontend:** http://diversiply.test
- **Admin:** http://diversiply.test/admin

Happy developing! 🚀
