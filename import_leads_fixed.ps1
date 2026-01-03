# Script to import leads.sql with proper row format settings

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = "C:\xampp\htdocs\bansalcrm\leads.sql"
$databaseName = "bansalc_db2"
$mysqlUser = "root"
$mysqlPassword = ""

Write-Host "========================================" -ForegroundColor Green
Write-Host "Importing leads.sql with ROW_FORMAT fix" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database: $databaseName" -ForegroundColor Yellow
Write-Host "SQL File: $sqlFile" -ForegroundColor Yellow
Write-Host ""

# Check files
if (-not (Test-Path $sqlFile)) {
    Write-Host "Error: SQL file not found!" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $mysqlPath)) {
    Write-Host "Error: MySQL not found!" -ForegroundColor Red
    exit 1
}

# Step 1: Ensure database exists
Write-Host "Step 1: Ensuring database exists..." -ForegroundColor Cyan
$createCmd = "CREATE DATABASE IF NOT EXISTS `"$databaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($mysqlPassword) {
    & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $createCmd 2>&1 | Out-Null
} else {
    & $mysqlPath -u $mysqlUser -e $createCmd 2>&1 | Out-Null
}
Write-Host "Database ready!" -ForegroundColor Green
Write-Host ""

# Step 2: Drop leads table if exists
Write-Host "Step 2: Dropping existing 'leads' table..." -ForegroundColor Cyan
$dropCmd = "DROP TABLE IF EXISTS `"$databaseName`".`leads`;"
if ($mysqlPassword) {
    & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $dropCmd 2>&1 | Out-Null
} else {
    & $mysqlPath -u $mysqlUser -e $dropCmd 2>&1 | Out-Null
}
Write-Host "Done!" -ForegroundColor Green
Write-Host ""

# Step 3: Set session defaults and import
Write-Host "Step 3: Setting MySQL session defaults and importing..." -ForegroundColor Cyan
Write-Host "This may take a few minutes..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date

# Create a temporary SQL file with session settings + original SQL
$tempSqlFile = "$env:TEMP\leads_import_temp.sql"

# Read the original SQL file
$sqlContent = Get-Content $sqlFile -Raw -Encoding UTF8

# Prepend session settings to ensure ROW_FORMAT is used
$sessionSettings = @"
SET SESSION innodb_strict_mode = 0;
SET SESSION innodb_default_row_format = 'DYNAMIC';
"@

# Combine settings with SQL content
$fullSql = $sessionSettings + "`n" + $sqlContent

# Write to temp file
$fullSql | Out-File -FilePath $tempSqlFile -Encoding UTF8 -NoNewline

# Import using the temp file
if ($mysqlPassword) {
    Get-Content $tempSqlFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser -p$mysqlPassword --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName
} else {
    Get-Content $tempSqlFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName
}

$endTime = Get-Date
$duration = $endTime - $startTime

# Clean up temp file
Remove-Item $tempSqlFile -ErrorAction SilentlyContinue

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Import completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Time taken: $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
    Write-Host ""
    
    # Verify
    Write-Host "Verifying import..." -ForegroundColor Cyan
    $verifyCmd = "SELECT COUNT(*) as row_count FROM leads;"
    if ($mysqlPassword) {
        $result = & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $verifyCmd $databaseName 2>&1
    } else {
        $result = & $mysqlPath -u $mysqlUser -e $verifyCmd $databaseName 2>&1
    }
    
    $rowCount = $result | Select-String -Pattern "\d+" | Select-Object -First 1
    if ($rowCount) {
        Write-Host "Rows in 'leads' table: $($rowCount.Matches.Value)" -ForegroundColor Green
    }
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Import failed!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Exit code: $LASTEXITCODE" -ForegroundColor Red
    Write-Host ""
    Write-Host "Other tables in the database were not affected." -ForegroundColor Yellow
    exit 1
}

