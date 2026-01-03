# Script to check import status

$databaseName = "bansalc_db2"
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$mysqlUser = "root"
$mysqlPassword = ""

Write-Host "Checking import status..." -ForegroundColor Cyan
Write-Host ""

# Check if database exists and get table count
$checkCmd = "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$databaseName';"
if ($mysqlPassword) {
    $result = & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $checkCmd $databaseName 2>&1
} else {
    $result = & $mysqlPath -u $mysqlUser -e $checkCmd $databaseName 2>&1
}

if ($LASTEXITCODE -eq 0) {
    $tableCount = ($result | Select-String -Pattern "\d+" | Select-Object -First 1).Matches.Value
    Write-Host "Database: $databaseName" -ForegroundColor Green
    Write-Host "Tables imported: $tableCount" -ForegroundColor Green
    Write-Host ""
    
    # Check if fixed file exists (means preprocessing is done)
    if (Test-Path "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql.fixed") {
        Write-Host "Status: Preprocessing completed, import in progress or completed" -ForegroundColor Yellow
    } else {
        Write-Host "Status: Preprocessing in progress..." -ForegroundColor Yellow
    }
} else {
    Write-Host "Could not connect to database or database doesn't exist yet." -ForegroundColor Red
}

Write-Host ""
Write-Host "To see full progress, check the PowerShell window running the import." -ForegroundColor Cyan

