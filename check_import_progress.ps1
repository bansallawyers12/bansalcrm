# Quick script to check database import progress
$dbName = "bansalc_db2"
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"

if (-not (Test-Path $mysqlPath)) {
    $mysqlPath = "mysql.exe"
}

Write-Host "Checking import progress for database: $dbName" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

try {
    $result = & $mysqlPath -u root -e "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$dbName';" 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        $tableCount = ($result | Select-String -Pattern '\d+').Matches.Value
        Write-Host "Tables imported so far: $tableCount" -ForegroundColor Green
        
        # Get database size
        $sizeResult = & $mysqlPath -u root -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema = '$dbName';" 2>&1
        if ($LASTEXITCODE -eq 0) {
            $dbSize = ($sizeResult | Select-String -Pattern '\d+\.?\d*').Matches.Value
            Write-Host "Database size: $dbSize MB" -ForegroundColor Green
        }
        
        # Check if MySQL import process is still running
        $mysqlProcesses = Get-Process mysql -ErrorAction SilentlyContinue
        if ($mysqlProcesses) {
            Write-Host "MySQL import process is still running..." -ForegroundColor Yellow
        } else {
            Write-Host "MySQL import process appears to have completed." -ForegroundColor Green
        }
    } else {
        Write-Host "Could not connect to MySQL or database doesn't exist yet." -ForegroundColor Red
    }
} catch {
    Write-Host "Error checking progress: $_" -ForegroundColor Red
}

Write-Host ""
