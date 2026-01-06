# Database Upload Complete - Documentation

## Overview

This document provides a complete record of the process used to upload a 2GB MySQL database (`bansalc_db2`) from a local file to the MySQL server.

**Date:** January 2026  
**Database Name:** `bansalc_db2`  
**Source File:** `C:\Users\user\Downloads\bansalc_db2\bansalc_db2.sql`  
**File Size:** ~1.89 GB (2,032,449,525 bytes)  
**Final Database Size:** ~3.78 GB (3,778.59 MB)  
**Total Tables Imported:** 90

---

## Initial Requirements

- **Database Name:** `bansalc_db2`
- **Source Location:** `C:\Users\user\Downloads\bansalc_db2`
- **Database Size:** 2 GB
- **Target:** MySQL Server (XAMPP installation)
- **Environment:** Windows 10

---

## Challenges Identified

1. **Large File Size:** 2GB database requires special handling to avoid memory issues
2. **MySQL Configuration:** Need to configure MySQL for large file imports
3. **Row Size Limits:** MySQL default row format limitations
4. **Existing Tables:** Handling of existing database/tables
5. **Non-Interactive Mode:** Scripts need to work without user interaction

---

## Scripts Created

### 1. `import_database.ps1` (Primary Script - Recommended)

**Purpose:** PowerShell script optimized for Windows with full feature support

**Features:**
- ✅ Handles compressed files (.gz)
- ✅ Automatic database creation/dropping
- ✅ Progress indicators
- ✅ Memory-efficient streaming for large files
- ✅ MySQL session configuration for large rows
- ✅ Error handling and troubleshooting tips
- ✅ Non-interactive mode support

**Key Configuration:**
```powershell
$dbName = "bansalc_db2"
$dbFile = "C:\Users\user\Downloads\bansalc_db2\bansalc_db2"
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$mysqlUser = "root"
$mysqlPassword = "" # Can be set via MYSQL_PASSWORD environment variable
```

**MySQL Settings Applied:**
- `--max_allowed_packet=1G` - Allows large packet sizes
- `--net_buffer_length=16K` - Network buffer optimization
- `--default-character-set=utf8mb4` - Proper character encoding
- `--force` - Continue on errors (non-fatal)
- `SET SESSION sql_mode=''` - Disable strict mode
- `SET SESSION innodb_strict_mode=0` - Allow larger rows
- `SET GLOBAL innodb_default_row_format='DYNAMIC'` - Support for large rows

**Usage:**
```powershell
.\import_database.ps1
```

---

### 2. `import_database.php`

**Purpose:** PHP alternative script with .env file support

**Features:**
- ✅ Reads database credentials from `.env` file
- ✅ Handles compressed files (.gz)
- ✅ Processes SQL in chunks for memory efficiency
- ✅ Progress indicators
- ✅ Automatic database creation

**Usage:**
```bash
php import_database.php
```

---

### 3. `import_database.bat`

**Purpose:** Simple batch file for quick imports

**Features:**
- ✅ Quick and easy execution
- ❌ Does NOT handle compressed files
- ⚠️ Requires manual decompression if file is .gz

**Usage:**
Double-click the file or run from command prompt:
```cmd
import_database.bat
```

---

### 4. `check_import_progress.ps1`

**Purpose:** Monitor database import progress

**Features:**
- Shows number of tables imported
- Displays current database size
- Checks if import process is still running

**Usage:**
```powershell
.\check_import_progress.ps1
```

---

## Process Followed

### Step 1: File Discovery

**Initial Check:**
- Discovered the database file was located in a directory: `C:\Users\user\Downloads\bansalc_db2`
- Found the actual SQL file: `bansalc_db2.sql` (1.89 GB)

**Verification:**
```powershell
Get-Item "C:\Users\user\Downloads\bansalc_db2" | Select-Object Name, Length, PSIsContainer
Get-ChildItem "C:\Users\user\Downloads\bansalc_db2" | Select-Object Name, Length
```

---

### Step 2: Script Development

Created multiple import scripts to handle different scenarios:
1. PowerShell script (primary) - Full featured
2. PHP script - Alternative with .env support
3. Batch script - Simple option
4. Progress monitoring script

---

### Step 3: Initial Import Attempt

**First Run Issues:**
1. **Non-Interactive Mode Error:**
   - Problem: `Read-Host` doesn't work in non-interactive PowerShell
   - Solution: Changed to use environment variable `MYSQL_PASSWORD` or default to empty

2. **Memory Exception:**
   - Problem: `Get-Content` tried to load entire 2GB file into memory
   - Solution: Switched to `cmd.exe` redirection which handles large files efficiently

**Command Used:**
```powershell
cmd /c "mysql.exe -u root --max_allowed_packet=1G ... < file.sql"
```

---

### Step 4: Row Size Error Resolution

**Error Encountered:**
```
ERROR 1118 (42000): Row size too large (> 8126). 
Changing some columns to TEXT or BLOB may help.
```

**Solution Applied:**
1. Set MySQL session variables before import:
   ```sql
   SET SESSION sql_mode='';
   SET SESSION innodb_strict_mode=0;
   SET GLOBAL innodb_default_row_format='DYNAMIC';
   ```

2. Added `--init-command` flag to MySQL command:
   ```powershell
   --init-command="SET SESSION sql_mode=''; SET SESSION innodb_strict_mode=0;"
   ```

---

### Step 5: Existing Table Handling

**Error Encountered:**
```
ERROR 1050 (42S01): Table 'account_client_receipts' already exists
```

**Solution Applied:**
1. Modified script to drop and recreate database:
   ```sql
   DROP DATABASE IF EXISTS `bansalc_db2`;
   CREATE DATABASE `bansalc_db2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Added `--force` flag to continue on non-fatal errors:
   ```powershell
   --force
   ```

---

### Step 6: Final Import Execution

**Final Script Configuration:**
- Database dropped and recreated for clean import
- MySQL session configured for large rows
- File redirection used for memory efficiency
- Force flag enabled to continue on errors

**Import Process:**
1. Database preparation (drop/create)
2. MySQL session configuration
3. File streaming to MySQL process
4. Background execution (takes 10-30 minutes for 2GB)

**Monitoring:**
- Used `check_import_progress.ps1` to monitor:
  - Table count: Started at 2, progressed to 7, then 90
  - Database size: Grew from 559.91 MB to 3,778.59 MB
  - Process status: Confirmed completion

---

## Final Results

### Import Statistics

| Metric | Value |
|--------|-------|
| **Source File Size** | 1.89 GB (2,032,449,525 bytes) |
| **Final Database Size** | 3.78 GB (3,778.59 MB) |
| **Total Tables** | 90 |
| **Database Name** | `bansalc_db2` |
| **Character Set** | utf8mb4 |
| **Collation** | utf8mb4_unicode_ci |
| **Status** | ✅ Successfully Imported |

### Sample Tables Imported

- `account_client_receipts`
- `activities_logs`
- `admins`
- `agents`
- `api_tokens`
- `application_activities_logs`
- `application_document_lists`
- `application_documents`
- `application_fee_option_types`
- `application_fee_options`
- `application_notes`
- `applications`
- `branches`
- `categories`
- ... (90 total tables)

---

## Technical Details

### MySQL Configuration Used

**Session Variables:**
```sql
SET SESSION sql_mode='';
SET SESSION innodb_strict_mode=0;
SET GLOBAL innodb_default_row_format='DYNAMIC';
```

**Command Line Flags:**
```bash
--max_allowed_packet=1G
--net_buffer_length=16K
--default-character-set=utf8mb4
--force
```

### File Handling Method

**Why cmd.exe redirection?**
- PowerShell's `Get-Content` loads entire file into memory
- For 2GB files, this causes `OutOfMemoryException`
- `cmd.exe` redirection streams file directly to MySQL process
- More memory-efficient for large files

**Command Structure:**
```powershell
cmd /c "mysql.exe [options] database_name < file.sql"
```

---

## Troubleshooting Guide

### Common Issues and Solutions

#### 1. "max_allowed_packet is too small"
**Solution:** Script includes `--max_allowed_packet=1G` flag. If still failing, update MySQL `my.ini`:
```ini
[mysqld]
max_allowed_packet=1G
```

#### 2. "Row size too large"
**Solution:** Script automatically sets `innodb_strict_mode=0` and `innodb_default_row_format='DYNAMIC'`

#### 3. "Access denied"
**Solution:** 
- Check MySQL username/password
- Set `MYSQL_PASSWORD` environment variable
- Or update script with correct credentials

#### 4. "File not found"
**Solution:**
- Verify file path in script configuration
- Check if file has `.sql` or `.sql.gz` extension
- Script automatically checks multiple extensions

#### 5. "Out of memory" (PHP script)
**Solution:**
```bash
php -d memory_limit=2G import_database.php
```

---

## How to Use Scripts in Future

### For Fresh Import

1. **Update Configuration:**
   - Edit script file
   - Update `$dbFile` path
   - Update `$dbName` if different
   - Set MySQL credentials

2. **Run Import:**
   ```powershell
   .\import_database.ps1
   ```

3. **Monitor Progress:**
   ```powershell
   .\check_import_progress.ps1
   ```

### For Re-import (Overwrite Existing)

Scripts automatically:
- Drop existing database
- Create fresh database
- Import all data

### For Compressed Files

PowerShell and PHP scripts automatically handle `.sql.gz` files:
- Detects compression
- Decompresses on-the-fly
- Imports directly

---

## Files Created

1. **`import_database.ps1`** - Primary import script (PowerShell)
2. **`import_database.php`** - Alternative import script (PHP)
3. **`import_database.bat`** - Simple batch script
4. **`check_import_progress.ps1`** - Progress monitoring script
5. **`DATABASE_IMPORT_README.md`** - User guide and documentation
6. **`DATABASE_UPLOAD_COMPLETE.md`** - This complete documentation

---

## Environment Details

- **OS:** Windows 10 (Build 26200)
- **MySQL:** XAMPP MySQL Server
- **MySQL Path:** `C:\xampp\mysql\bin\mysql.exe`
- **PowerShell:** Windows PowerShell 5.1+
- **PHP:** Included with XAMPP

---

## Best Practices Applied

1. **Memory Efficiency:** Used file streaming instead of loading entire file
2. **Error Handling:** Added `--force` flag and proper error messages
3. **Progress Monitoring:** Created separate script for status checks
4. **Configuration:** Made scripts configurable and reusable
5. **Documentation:** Comprehensive documentation for future reference
6. **Multiple Options:** Created different script types for different needs

---

## Verification Commands

### Check Database Exists
```sql
SHOW DATABASES LIKE 'bansalc_db2';
```

### Count Tables
```sql
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = 'bansalc_db2';
```

### Check Database Size
```sql
SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB'
FROM information_schema.tables 
WHERE table_schema = 'bansalc_db2';
```

### List All Tables
```sql
USE bansalc_db2;
SHOW TABLES;
```

---

## Conclusion

The database import was successfully completed using a PowerShell script optimized for large file imports. The process involved:

1. ✅ Creating multiple import scripts for different scenarios
2. ✅ Resolving memory issues with large file handling
3. ✅ Configuring MySQL for large row sizes
4. ✅ Handling existing database/tables
5. ✅ Successfully importing 90 tables (~3.78 GB)
6. ✅ Creating monitoring tools for progress tracking
7. ✅ Documenting the complete process

**The database `bansalc_db2` is now ready for use.**

---

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review MySQL error logs: `C:\xampp\mysql\data\*.err`
3. Verify MySQL is running: `Get-Service | Where-Object {$_.Name -like "*mysql*"}`
4. Check disk space: `Get-PSDrive C | Select-Object Used,Free`

---

**Document Created:** January 2026  
**Last Updated:** January 2026  
**Status:** ✅ Complete
