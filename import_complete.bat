@echo off
REM Complete database import - reliable method for large files

set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set SQL_FILE=C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql.fixed
set DATABASE_NAME=bansalc_db2
set MYSQL_USER=root
set MYSQL_PASSWORD=

echo ========================================
echo Complete Database Import
echo ========================================
echo Database: %DATABASE_NAME%
echo SQL File: %SQL_FILE%
echo.

REM Check if fixed file exists, if not use original
if not exist "%SQL_FILE%" (
    set SQL_FILE=C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql
    echo Using original file: %SQL_FILE%
)

if not exist "%SQL_FILE%" (
    echo ERROR: SQL file not found!
    pause
    exit /b 1
)

REM Step 1: Drop and recreate database
echo Step 1: Preparing database (dropping existing)...
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "DROP DATABASE IF EXISTS `"%DATABASE_NAME%`";" 2>nul
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "CREATE DATABASE `"%DATABASE_NAME%`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "DROP DATABASE IF EXISTS `"%DATABASE_NAME%`";" 2>nul
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "CREATE DATABASE `"%DATABASE_NAME%`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
)

echo Database prepared!
echo.

REM Step 2: Import SQL file
echo Step 2: Importing SQL file...
echo WARNING: This will take 30 minutes to several hours for a 2GB file.
echo Please wait and do not close this window...
echo.

REM Use input redirection - most reliable for large files
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 --force %DATABASE_NAME% < "%SQL_FILE%" 2> import_errors.log
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 --force %DATABASE_NAME% < "%SQL_FILE%" 2> import_errors.log
)

if errorlevel 1 (
    echo.
    echo Import completed with some errors (check import_errors.log)
) else (
    echo.
    echo Import completed successfully!
)

REM Check final table count
echo.
echo Checking final status...
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = '%DATABASE_NAME%';" %DATABASE_NAME% 2>nul
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = '%DATABASE_NAME%';" %DATABASE_NAME% 2>nul
)

echo.
echo ========================================
if exist import_errors.log (
    echo Check import_errors.log for any errors
    echo.
    findstr /C:"ERROR" import_errors.log | findstr /V /C:"Warning" | more
)
echo ========================================
pause

