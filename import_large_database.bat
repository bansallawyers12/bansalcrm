@echo off
REM Batch script to import large SQL file (2GB) into MySQL database
REM This is more memory-efficient for very large files

set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set SQL_FILE=C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql
set DATABASE_NAME=bansalc_db2
set MYSQL_USER=root
set MYSQL_PASSWORD=

echo ========================================
echo Large Database Import Script
echo ========================================
echo Database: %DATABASE_NAME%
echo SQL File: %SQL_FILE%
echo.

REM Check if SQL file exists
if not exist "%SQL_FILE%" (
    echo ERROR: SQL file not found at %SQL_FILE%
    pause
    exit /b 1
)

REM Check if MySQL exists
if not exist "%MYSQL_PATH%" (
    echo ERROR: MySQL not found at %MYSQL_PATH%
    pause
    exit /b 1
)

echo Step 1: Creating database if it doesn't exist...
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "CREATE DATABASE IF NOT EXISTS `"%DATABASE_NAME%`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "CREATE DATABASE IF NOT EXISTS `"%DATABASE_NAME%`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
)

if errorlevel 1 (
    echo ERROR: Failed to create database. Please check MySQL credentials.
    pause
    exit /b 1
)

echo Database created/verified successfully!
echo.

echo Step 2: Setting MySQL configuration for large rows...
REM Set innodb_default_row_format to DYNAMIC to handle large rows
REM Note: innodb_file_format is deprecated in MySQL 8.0+, so we skip it
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "SET GLOBAL innodb_default_row_format='DYNAMIC'; SET GLOBAL innodb_large_prefix=1;" 2>nul
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "SET GLOBAL innodb_default_row_format='DYNAMIC'; SET GLOBAL innodb_large_prefix=1;" 2>nul
)

echo Step 3: Importing SQL file...
echo WARNING: This may take 30 minutes to several hours for a 2GB file.
echo Please wait and do not close this window...
echo.

REM Import with optimized settings for large files
REM Using input redirection which is more memory-efficient for large files
REM Note: innodb settings are configured in my.ini, not command line
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 %DATABASE_NAME% < "%SQL_FILE%"
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 %DATABASE_NAME% < "%SQL_FILE%"
)

if errorlevel 1 (
    echo.
    echo ERROR: Import failed. Please check the error messages above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Import completed successfully!
echo ========================================
pause

