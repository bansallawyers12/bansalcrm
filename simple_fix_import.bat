@echo off
REM Simple script to fix and import large SQL file
REM Uses PowerShell for efficient processing

set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set SQL_FILE=C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql
set DATABASE_NAME=bansalc_db2
set MYSQL_USER=root
set MYSQL_PASSWORD=

echo ========================================
echo Database Import Script
echo ========================================
echo Database: %DATABASE_NAME%
echo SQL File: %SQL_FILE%
echo.

REM Step 1: Create database
echo Step 1: Creating database...
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% -e "CREATE DATABASE IF NOT EXISTS `"%DATABASE_NAME%`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% -e "CREATE DATABASE IF NOT EXISTS `"%DATABASE_NAME%`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
)

echo Database ready!
echo.

REM Step 2: Use PowerShell to fix and import
echo Step 2: Importing with ROW_FORMAT fix...
echo This will take a long time for a 2GB file. Please wait...
echo.

powershell -Command "$reader = [System.IO.StreamReader]::new('%SQL_FILE%'); $process = Start-Process -FilePath '%MYSQL_PATH%' -ArgumentList @('-u', '%MYSQL_USER%', '--max_allowed_packet=1073741824', '--net_buffer_length=1048576', '--default-character-set=utf8mb4', '%DATABASE_NAME%') -NoNewWindow -PassThru -RedirectStandardInput $true; $stdin = $process.StandardInput; $lineCount = 0; $fixed = 0; $inTable = $false; $tableBuffer = ''; while ($null -ne ($line = $reader.ReadLine())) { $lineCount++; if ($line -match '^\s*CREATE\s+TABLE' -and -not $inTable) { $inTable = $true; $tableBuffer = $line } elseif ($inTable) { $tableBuffer += \"`n\" + $line; if ($line -match 'ENGINE\s*=' -or ($line -match '\)\s*;' -and $line -notmatch 'ROW_FORMAT')) { if ($tableBuffer -notmatch 'ROW_FORMAT') { if ($line -match 'ENGINE\s*=') { $line = $line -replace '(ENGINE\s*=)', 'ROW_FORMAT=DYNAMIC $1' } elseif ($line -match '\)\s*;') { $line = $line -replace '(\)\s*;)', ') ROW_FORMAT=DYNAMIC;' } $tableBuffer = $tableBuffer -replace '(?s)(.*\n)(.*)$', \"$1$line\"; $fixed++ } $stdin.WriteLine($tableBuffer); $inTable = $false; $tableBuffer = '' } } else { $stdin.WriteLine($line) } if ($lineCount %% 50000 -eq 0) { Write-Host \"Processed $lineCount lines, fixed $fixed tables...\" } }; $stdin.Close(); $process.WaitForExit(); $reader.Close(); if ($process.ExitCode -eq 0) { Write-Host \"Import completed!\" -ForegroundColor Green } else { Write-Host \"Import failed with code: $($process.ExitCode)\" -ForegroundColor Red; exit 1 }"

if errorlevel 1 (
    echo.
    echo Import failed. Trying alternative method...
    echo.
    goto :alternative
) else (
    echo.
    echo ========================================
    echo Import completed successfully!
    echo ========================================
    pause
    exit /b 0
)

:alternative
echo Using alternative import method (may be slower)...
echo This will create a fixed SQL file first...
echo.

REM Alternative: Create fixed file using PowerShell
powershell -ExecutionPolicy Bypass -Command "$input = '%SQL_FILE%'; $output = '%SQL_FILE%.fixed'; $reader = [System.IO.StreamReader]::new($input); $writer = [System.IO.StreamWriter]::new($output); $count = 0; $fixed = 0; $inTable = $false; $tableLines = @(); while ($null -ne ($line = $reader.ReadLine())) { $count++; if ($line -match '^\s*CREATE\s+TABLE' -and -not $inTable) { $inTable = $true; $tableLines = @($line) } elseif ($inTable) { $tableLines += $line; if ($line -match 'ENGINE\s*=' -or ($line -match '\)\s*;' -and $line -notmatch 'ROW_FORMAT')) { $tableDef = $tableLines -join \"`n\"; if ($tableDef -notmatch 'ROW_FORMAT') { if ($line -match 'ENGINE\s*=') { $line = $line -replace '(ENGINE\s*=)', 'ROW_FORMAT=DYNAMIC $1' } elseif ($line -match '\)\s*;') { $line = $line -replace '(\)\s*;)', ') ROW_FORMAT=DYNAMIC;' } $tableLines[$tableLines.Length-1] = $line; $fixed++ } $writer.WriteLine(($tableLines -join \"`n\")); $inTable = $false; $tableLines = @() } } else { $writer.WriteLine($line) } if ($count %% 100000 -eq 0) { Write-Host \"Processed $count lines, fixed $fixed tables...\" } }; $reader.Close(); $writer.Close(); Write-Host \"Fixed file created: $output\" -ForegroundColor Green; Write-Host \"Fixed $fixed tables\" -ForegroundColor Green"

echo.
echo Now importing fixed file...
if "%MYSQL_PASSWORD%"=="" (
    "%MYSQL_PATH%" -u %MYSQL_USER% --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 %DATABASE_NAME% < "%SQL_FILE%.fixed"
) else (
    "%MYSQL_PATH%" -u %MYSQL_USER% -p%MYSQL_PASSWORD% --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 %DATABASE_NAME% < "%SQL_FILE%.fixed"
)

if errorlevel 1 (
    echo.
    echo ERROR: Import failed.
    pause
    exit /b 1
) else (
    echo.
    echo ========================================
    echo Import completed successfully!
    echo ========================================
    echo You can delete the fixed file: %SQL_FILE%.fixed
    pause
)

