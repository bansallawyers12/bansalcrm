@echo off
REM Batch Script to Import Large MySQL Database
REM Database: bansalc_db2
REM File: C:\Users\user\Downloads\bansalc_db2

echo ========================================
echo MySQL Database Import Script
echo ========================================
echo.

REM Configuration
set DB_NAME=bansalc_db2
set DB_FILE=C:\Users\user\Downloads\bansalc_db2
set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set MYSQL_USER=root
set MYSQL_PASSWORD=

REM Check if MySQL path exists
if not exist "%MYSQL_PATH%" (
    set MYSQL_PATH=mysql.exe
    echo Using system PATH for mysql.exe
)

REM Find database file
set DB_FILE_PATH=%DB_FILE%
if not exist "%DB_FILE_PATH%" (
    set DB_FILE_PATH=%DB_FILE%.sql
    if not exist "%DB_FILE_PATH%" (
        set DB_FILE_PATH=%DB_FILE%.sql.gz
        if not exist "%DB_FILE_PATH%" (
            echo ERROR: Database file not found
            echo   %DB_FILE%
            echo   %DB_FILE%.sql
            echo   %DB_FILE%.sql.gz
            pause
            exit /b 1
        )
    )
)

echo Database File: %DB_FILE_PATH%
echo.

REM Check if file is compressed
echo %DB_FILE_PATH% | findstr /i "\.gz$" >nul
if %errorlevel% equ 0 (
    echo ERROR: Compressed files (.gz) are not supported in batch script.
    echo Please use import_database.ps1 or import_database.php instead.
    echo Or decompress the file first using 7-Zip or similar tool.
    pause
    exit /b 1
)

REM Prompt for password if not set
if "%MYSQL_PASSWORD%"=="" (
    set /p MYSQL_PASSWORD="Enter MySQL password (or press Enter for no password): "
)

REM Create database
echo Creating database '%DB_NAME%' if it doesn't exist...
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%`"
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "CREATE DATABASE IF NOT EXISTS `%DB_NAME%`"
)

if %errorlevel% neq 0 (
    echo Warning: Could not create database (it may already exist)
)
echo.

REM Import database
echo Starting database import...
echo This may take a while for a 2GB database. Please be patient...
echo.

set START_TIME=%time%

if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% --max_allowed_packet=1G --net_buffer_length=16K --default-character-set=utf8mb4 %DB_NAME% < "%DB_FILE_PATH%"
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% --max_allowed_packet=1G --net_buffer_length=16K --default-character-set=utf8mb4 %DB_NAME% < "%DB_FILE_PATH%"
)

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Import completed successfully!
    echo ========================================
) else (
    echo.
    echo ERROR: Import failed!
    echo.
    echo Troubleshooting tips:
    echo 1. Check MySQL is running
    echo 2. Verify database credentials
    echo 3. Check MySQL max_allowed_packet setting (should be at least 1G)
    echo 4. Ensure you have enough disk space
    echo 5. Check MySQL error log for details
)

echo.
pause
