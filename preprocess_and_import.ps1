# Simple script to preprocess SQL file and import
# This creates a fixed version of the SQL file with ROW_FORMAT=DYNAMIC

param(
    [string]$SqlFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql",
    [string]$DatabaseName = "bansalc_db2",
    [string]$MysqlUser = "root",
    [string]$MysqlPassword = "",
    [switch]$SkipPreprocess
)

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$fixedFile = $SqlFile + ".fixed"

Write-Host "========================================" -ForegroundColor Green
Write-Host "Database Import Script" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database: $DatabaseName" -ForegroundColor Yellow
Write-Host "SQL File: $SqlFile" -ForegroundColor Yellow
Write-Host ""

# Check files
if (-not (Test-Path $SqlFile)) {
    Write-Host "Error: SQL file not found!" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $mysqlPath)) {
    Write-Host "Error: MySQL not found!" -ForegroundColor Red
    exit 1
}

# Step 1: Create database
Write-Host "Step 1: Creating database..." -ForegroundColor Cyan
$createCmd = "CREATE DATABASE IF NOT EXISTS `"$DatabaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($MysqlPassword) {
    & $mysqlPath -u $MysqlUser -p$MysqlPassword -e $createCmd 2>&1 | Out-Null
} else {
    & $mysqlPath -u $MysqlUser -e $createCmd 2>&1 | Out-Null
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error creating database!" -ForegroundColor Red
    exit 1
}
Write-Host "Database created successfully!" -ForegroundColor Green
Write-Host ""

# Step 2: Preprocess SQL file (if needed)
if (-not $SkipPreprocess -and -not (Test-Path $fixedFile)) {
    Write-Host "Step 2: Preprocessing SQL file to add ROW_FORMAT=DYNAMIC..." -ForegroundColor Cyan
    Write-Host "This may take 10-30 minutes for a 2GB file. Please wait..." -ForegroundColor Yellow
    Write-Host ""
    
    $reader = [System.IO.StreamReader]::new($SqlFile)
    $writer = [System.IO.StreamWriter]::new($fixedFile)
    
    $lineCount = 0
    $fixedCount = 0
    $inCreateTable = $false
    $tableLines = New-Object System.Collections.ArrayList
    
    try {
        while ($null -ne ($line = $reader.ReadLine())) {
            $lineCount++
            
            if ($line -match "^\s*CREATE\s+TABLE" -and -not $inCreateTable) {
                $inCreateTable = $true
                $tableLines.Clear() | Out-Null
                $tableLines.Add($line) | Out-Null
            }
            elseif ($inCreateTable) {
                $tableLines.Add($line) | Out-Null
                
                # Check for end of CREATE TABLE
                if ($line -match "ENGINE\s*=" -or ($line -match "\)\s*;" -and $line -notmatch "ROW_FORMAT")) {
                    # Check if ROW_FORMAT is missing
                    $tableDef = $tableLines -join "`n"
                    if ($tableDef -notmatch "ROW_FORMAT") {
                        $fixedCount++
                        $lastIdx = $tableLines.Count - 1
                        $lastLine = $tableLines[$lastIdx]
                        
                        if ($lastLine -match "ENGINE\s*=") {
                            $tableLines[$lastIdx] = $lastLine -replace "(ENGINE\s*=)", "ROW_FORMAT=DYNAMIC `$1"
                        }
                        elseif ($lastLine -match "\)\s*;") {
                            $tableLines[$lastIdx] = $lastLine -replace "(\)\s*;)", ") ROW_FORMAT=DYNAMIC;"
                        }
                    }
                    
                    # Write the table definition
                    foreach ($tableLine in $tableLines) {
                        $writer.WriteLine($tableLine)
                    }
                    
                    $inCreateTable = $false
                    $tableLines.Clear() | Out-Null
                }
            }
            else {
                $writer.WriteLine($line)
            }
            
            if ($lineCount % 100000 -eq 0) {
                Write-Host "Processed $lineCount lines, fixed $fixedCount tables..." -ForegroundColor Cyan
            }
        }
        
        Write-Host ""
        Write-Host "Preprocessing completed!" -ForegroundColor Green
        Write-Host "Total lines: $lineCount" -ForegroundColor Green
        Write-Host "Tables fixed: $fixedCount" -ForegroundColor Green
        Write-Host "Fixed file: $fixedFile" -ForegroundColor Green
        Write-Host ""
        
    } finally {
        $reader.Close()
        $writer.Close()
    }
} elseif (Test-Path $fixedFile) {
    Write-Host "Step 2: Using existing fixed file: $fixedFile" -ForegroundColor Cyan
    Write-Host ""
}

# Step 3: Import
$importFile = if (Test-Path $fixedFile) { $fixedFile } else { $SqlFile }

Write-Host "Step 3: Importing SQL file..." -ForegroundColor Cyan
Write-Host "WARNING: This may take 30 minutes to several hours for a 2GB file." -ForegroundColor Yellow
Write-Host "Please wait and do not close this window..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date

if ($MysqlPassword) {
    Get-Content $importFile -Encoding UTF8 | & $mysqlPath -u $MysqlUser -p$MysqlPassword --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $DatabaseName
} else {
    Get-Content $importFile -Encoding UTF8 | & $mysqlPath -u $MysqlUser --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $DatabaseName
}

$endTime = Get-Date
$duration = $endTime - $startTime

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Import completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Time taken: $($duration.Hours)h $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
    Write-Host ""
    if (Test-Path $fixedFile) {
        Write-Host "You can delete the fixed file: $fixedFile" -ForegroundColor Yellow
    }
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "Import failed!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    exit 1
}

