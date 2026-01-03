# Script to import leads.sql file

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = "C:\xampp\htdocs\bansalcrm\leads.sql"
$databaseName = "bansalc_db2"
$mysqlUser = "root"
$mysqlPassword = ""

Write-Host "========================================" -ForegroundColor Green
Write-Host "Importing leads.sql" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database: $databaseName" -ForegroundColor Yellow
Write-Host "SQL File: $sqlFile" -ForegroundColor Yellow
Write-Host "File Size: $([math]::Round((Get-Item $sqlFile).Length / 1MB, 2)) MB" -ForegroundColor Yellow
Write-Host ""

# Check if SQL file exists
if (-not (Test-Path $sqlFile)) {
    Write-Host "Error: SQL file not found!" -ForegroundColor Red
    exit 1
}

# Check if MySQL exists
if (-not (Test-Path $mysqlPath)) {
    Write-Host "Error: MySQL not found!" -ForegroundColor Red
    exit 1
}

# Check if database exists
Write-Host "Step 1: Verifying database exists..." -ForegroundColor Cyan
$checkCmd = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName';"
if ($mysqlPassword) {
    $result = & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $checkCmd 2>&1
} else {
    $result = & $mysqlPath -u $mysqlUser -e $checkCmd 2>&1
}

if ($result -notmatch $databaseName) {
    Write-Host "Database $databaseName does not exist. Creating it..." -ForegroundColor Yellow
    $createCmd = "CREATE DATABASE IF NOT EXISTS `"$databaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    if ($mysqlPassword) {
        & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $createCmd 2>&1 | Out-Null
    } else {
        & $mysqlPath -u $mysqlUser -e $createCmd 2>&1 | Out-Null
    }
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Error creating database!" -ForegroundColor Red
        exit 1
    }
}
Write-Host "Database verified!" -ForegroundColor Green
Write-Host ""

# Import the SQL file
Write-Host "Step 2: Importing leads.sql..." -ForegroundColor Cyan
Write-Host "This should take only a few minutes for a 6.5 MB file..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date

if ($mysqlPassword) {
    Get-Content $sqlFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser -p$mysqlPassword --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName
} else {
    Get-Content $sqlFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName
}

$endTime = Get-Date
$duration = $endTime - $startTime

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Import completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Time taken: $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
    Write-Host ""
    
    # Verify the import
    Write-Host "Verifying import..." -ForegroundColor Cyan
    $verifyCmd = "SELECT COUNT(*) as row_count FROM leads;"
    if ($mysqlPassword) {
        $rowCount = & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $verifyCmd $databaseName 2>&1 | Select-String -Pattern "\d+" | Select-Object -First 1
    } else {
        $rowCount = & $mysqlPath -u $mysqlUser -e $verifyCmd $databaseName 2>&1 | Select-String -Pattern "\d+" | Select-Object -First 1
    }
    
    if ($rowCount) {
        Write-Host "Rows imported into 'leads' table: $($rowCount.Matches.Value)" -ForegroundColor Green
    }
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Import failed!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Exit code: $LASTEXITCODE" -ForegroundColor Red
    exit 1
}

