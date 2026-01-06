# Database Import Scripts

This directory contains scripts to import a large 2GB MySQL database (`bansalc_db2`) from `C:\Users\user\Downloads\bansalc_db2`.

## Available Scripts

### 1. `import_database.ps1` (Recommended for Windows)
PowerShell script with the most features:
- ✅ Handles compressed files (.gz)
- ✅ Progress indicators
- ✅ Automatic database creation
- ✅ Optimized for large files (2GB+)
- ✅ Error handling and troubleshooting tips

**Usage:**
```powershell
.\import_database.ps1
```

**Or right-click and select "Run with PowerShell"**

### 2. `import_database.php`
PHP script alternative:
- ✅ Handles compressed files (.gz)
- ✅ Reads database credentials from `.env` file
- ✅ Progress indicators
- ✅ Processes SQL in chunks for memory efficiency

**Usage:**
```bash
php import_database.php
```

### 3. `import_database.bat`
Simple batch file:
- ✅ Quick and easy
- ❌ Does NOT handle compressed files
- ⚠️ Requires manual decompression if file is .gz

**Usage:**
Double-click the file or run from command prompt:
```cmd
import_database.bat
```

## Configuration

Before running, you may need to update these settings in the scripts:

1. **Database Credentials:**
   - MySQL Username (default: `root`)
   - MySQL Password (default: empty)
   - MySQL Host (default: `127.0.0.1`)
   - MySQL Port (default: `3306`)

2. **File Path:**
   - Database file: `C:\Users\user\Downloads\bansalc_db2`
   - The script will automatically check for `.sql` and `.sql.gz` extensions

3. **MySQL Path:**
   - Default: `C:\xampp\mysql\bin\mysql.exe`
   - Scripts will try to use system PATH if XAMPP path doesn't exist

## Prerequisites

1. **MySQL Server** must be running
2. **MySQL Command Line Tools** must be installed (included with XAMPP)
3. **Sufficient disk space** (at least 2GB free)
4. **PHP** (for PHP script) - included with XAMPP

## MySQL Configuration for Large Files

For importing large databases, ensure MySQL is configured properly:

1. **Edit `my.ini` or `my.cnf`** (usually in `C:\xampp\mysql\bin\` or `C:\xampp\mysql\`):
   ```ini
   [mysqld]
   max_allowed_packet=1G
   net_buffer_length=16K
   ```

2. **Restart MySQL** after making changes

3. **Or set temporarily** (scripts include `--max_allowed_packet=1G` flag)

## Troubleshooting

### Error: "max_allowed_packet is too small"
- Solution: Increase `max_allowed_packet` in MySQL configuration (see above)

### Error: "Access denied"
- Solution: Check MySQL username and password in script configuration

### Error: "File not found"
- Solution: Verify the database file path is correct
- Check if file has `.sql` or `.sql.gz` extension

### Import is very slow
- This is normal for 2GB databases
- Ensure MySQL is running on localhost (not remote)
- Close other applications to free up resources
- Check disk I/O performance

### Out of memory errors (PHP script)
- Increase PHP memory limit: `php -d memory_limit=2G import_database.php`

## File Formats Supported

- `.sql` - Plain SQL dump file
- `.sql.gz` - Compressed SQL dump file (PowerShell and PHP scripts only)

## Notes

- The scripts will create the database if it doesn't exist
- Existing data in the database will be overwritten
- Import time varies but expect 10-30 minutes for a 2GB database
- Scripts include progress indicators for long-running imports

## Recommended Approach

1. **First try:** `import_database.ps1` (most features, best for Windows)
2. **If PowerShell issues:** Use `import_database.php`
3. **If file is already decompressed:** Use `import_database.bat` for simplicity
