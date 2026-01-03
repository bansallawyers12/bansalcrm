# Script to resume database import from where it stopped
# This will skip tables that already exist and continue importing

param(
    [string]$SqlFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql.fixed",
    [string]$DatabaseName = "bansalc_db2",
    [string]$MysqlUser = "root",
    [string]$MysqlPassword = ""
)

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"

Write-Host "========================================" -ForegroundColor Green
Write-Host "Resume Database Import" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database: $DatabaseName" -ForegroundColor Yellow
Write-Host "SQL File: $SqlFile" -ForegroundColor Yellow
Write-Host ""

# Check if fixed file exists, if not use original
if (-not (Test-Path $SqlFile)) {
    $SqlFile = $SqlFile -replace "\.fixed$", ""
    Write-Host "Fixed file not found, using original: $SqlFile" -ForegroundColor Yellow
}

if (-not (Test-Path $SqlFile)) {
    Write-Host "Error: SQL file not found!" -ForegroundColor Red
    exit 1
}

# Get list of existing tables
Write-Host "Step 1: Checking existing tables..." -ForegroundColor Cyan
$existingTablesCmd = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '$DatabaseName';"
if ($MysqlPassword) {
    $existingTables = & $mysqlPath -u $MysqlUser -p$MysqlPassword -e $existingTablesCmd $DatabaseName 2>&1 | Select-Object -Skip 1
} else {
    $existingTables = & $mysqlPath -u $MysqlUser -e $existingTablesCmd $DatabaseName 2>&1 | Select-Object -Skip 1
}

$existingTableSet = New-Object System.Collections.Generic.HashSet[string]
foreach ($table in $existingTables) {
    $table = $table.Trim()
    if ($table -and $table -notmatch "^\+" -and $table -notmatch "TABLE_NAME") {
        $existingTableSet.Add($table) | Out-Null
    }
}

Write-Host "Found $($existingTableSet.Count) existing tables" -ForegroundColor Green
Write-Host ""

# Read SQL file and filter out existing tables
Write-Host "Step 2: Processing SQL file and skipping existing tables..." -ForegroundColor Cyan
Write-Host "This may take a while. Please wait..." -ForegroundColor Yellow
Write-Host ""

$reader = [System.IO.StreamReader]::new($SqlFile)
$tempFile = "$env:TEMP\resume_import_$(Get-Date -Format 'yyyyMMddHHmmss').sql"
$writer = [System.IO.StreamWriter]::new($tempFile)

$lineCount = 0
$skippedTables = 0
$inCreateTable = $false
$currentTableName = ""
$tableLines = New-Object System.Collections.ArrayList
$skipCurrentTable = $false

try {
    while ($null -ne ($line = $reader.ReadLine())) {
        $lineCount++
        
        # Detect CREATE TABLE statement
        if ($line -match "^\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`\""]?(\w+)[`\""]?") {
            $inCreateTable = $true
            $currentTableName = $matches[1]
            $tableLines.Clear() | Out-Null
            $tableLines.Add($line) | Out-Null
            
            # Check if table already exists
            $skipCurrentTable = $existingTableSet.Contains($currentTableName)
            if ($skipCurrentTable) {
                $skippedTables++
                Write-Host "Skipping existing table: $currentTableName" -ForegroundColor Yellow
            }
        }
        elseif ($inCreateTable) {
            $tableLines.Add($line) | Out-Null
            
            # Check if this is the end of CREATE TABLE
            if ($line -match "ENGINE\s*=" -or $line -match "\)\s*;") {
                if (-not $skipCurrentTable) {
                    # Write the table definition
                    foreach ($tableLine in $tableLines) {
                        $writer.WriteLine($tableLine)
                    }
                }
                
                $inCreateTable = $false
                $currentTableName = ""
                $tableLines.Clear() | Out-Null
                $skipCurrentTable = $false
            }
        }
        elseif (-not $inCreateTable) {
            # Write non-table statements (INSERT, etc.) only if we're not skipping
            # For now, write everything - we'll handle data separately if needed
            $writer.WriteLine($line)
        }
        
        if ($lineCount % 100000 -eq 0) {
            Write-Host "Processed $lineCount lines, skipped $skippedTables tables..." -ForegroundColor Cyan
        }
    }
    
    Write-Host ""
    Write-Host "Processing completed!" -ForegroundColor Green
    Write-Host "Total lines processed: $lineCount" -ForegroundColor Green
    Write-Host "Tables skipped (already exist): $skippedTables" -ForegroundColor Green
    Write-Host ""
    
} finally {
    $reader.Close()
    $writer.Close()
}

# Step 3: Import the filtered SQL
Write-Host "Step 3: Importing remaining tables and data..." -ForegroundColor Cyan
Write-Host "This may take a while. Please wait..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date

if ($MysqlPassword) {
    Get-Content $tempFile -Encoding UTF8 | & $mysqlPath -u $MysqlUser -p$MysqlPassword --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $DatabaseName 2>&1 | Tee-Object -Variable importOutput
} else {
    Get-Content $tempFile -Encoding UTF8 | & $mysqlPath -u $MysqlUser --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $DatabaseName 2>&1 | Tee-Object -Variable importOutput
}

$endTime = Get-Date
$duration = $endTime - $startTime

# Check for errors
$hasErrors = $false
if ($importOutput -match "ERROR") {
    Write-Host ""
    Write-Host "Errors found during import:" -ForegroundColor Red
    $importOutput | Select-String -Pattern "ERROR" | ForEach-Object { Write-Host $_ -ForegroundColor Red }
    $hasErrors = $true
}

# Check final table count
$finalCountCmd = "SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '$DatabaseName';"
if ($MysqlPassword) {
    $finalCount = & $mysqlPath -u $MysqlUser -p$MysqlPassword -e $finalCountCmd $DatabaseName 2>&1 | Select-String -Pattern "\d+" | Select-Object -First 1
} else {
    $finalCount = & $mysqlPath -u $MysqlUser -e $finalCountCmd $DatabaseName 2>&1 | Select-String -Pattern "\d+" | Select-Object -First 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
if ($LASTEXITCODE -eq 0 -and -not $hasErrors) {
    Write-Host "Import completed!" -ForegroundColor Green
    Write-Host "Total tables in database: $finalCount" -ForegroundColor Green
} else {
    Write-Host "Import completed with warnings/errors" -ForegroundColor Yellow
    Write-Host "Total tables in database: $finalCount" -ForegroundColor Yellow
}
Write-Host "Time taken: $($duration.Hours)h $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green

# Clean up temp file
Remove-Item $tempFile -ErrorAction SilentlyContinue

if ($LASTEXITCODE -eq 0) {
    exit 0
} else {
    exit 1
}

