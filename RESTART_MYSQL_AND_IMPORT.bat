@echo off
REM Script to restart MySQL and import database

echo ========================================
echo MySQL Configuration Updated!
echo ========================================
echo.
echo IMPORTANT: You need to restart MySQL for the changes to take effect.
echo.
echo Please do the following:
echo 1. Open XAMPP Control Panel
echo 2. Stop MySQL (click Stop button)
echo 3. Wait 5 seconds
echo 4. Start MySQL again (click Start button)
echo 5. Wait for MySQL to fully start (green indicator)
echo 6. Then run: import_large_database.bat
echo.
echo OR press any key to attempt automatic restart (requires admin rights)...
pause

REM Try to restart MySQL service (may require admin rights)
net stop mysql 2>nul
timeout /t 3 /nobreak >nul
net start mysql 2>nul

if errorlevel 1 (
    echo.
    echo Could not restart MySQL automatically.
    echo Please restart MySQL manually from XAMPP Control Panel.
    echo.
    pause
    exit /b 1
) else (
    echo.
    echo MySQL restarted successfully!
    echo Waiting 10 seconds for MySQL to fully start...
    timeout /t 10 /nobreak >nul
    echo.
    echo Now running import script...
    echo.
    call import_large_database.bat
)

