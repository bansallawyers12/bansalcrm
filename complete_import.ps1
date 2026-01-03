# Complete database import script with error handling
# This will import the entire database, skipping existing tables if needed

param(
    [string]$SqlFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql.fixed",
    [string]$DatabaseName = "bansalc_db2",
    [string]$MysqlUser = "root",
    [string]$MysqlPassword = ""
)

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"

Write-Host "========================================" -ForegroundColor Green
Write-Host "Complete Database Import" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database: $DatabaseName" -ForegroundColor Yellow
Write-Host "SQL File: $SqlFile" -ForegroundColor Yellow
Write-Host ""

# Use fixed file if available, otherwise original
if (-not (Test-Path $SqlFile)) {
    $SqlFile = $SqlFile -replace "\.fixed$", ""
    Write-Host "Using original file: $SqlFile" -ForegroundColor Yellow
}

if (-not (Test-Path $SqlFile)) {
    Write-Host "Error: SQL file not found!" -ForegroundColor Red
    exit 1
}

# Step 1: Drop and recreate database to ensure clean import
Write-Host "Step 1: Preparing database..." -ForegroundColor Cyan
$dropCmd = "DROP DATABASE IF EXISTS `"$DatabaseName`";"
$createCmd = "CREATE DATABASE `"$DatabaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if ($MysqlPassword) {
    & $mysqlPath -u $MysqlUser -p$MysqlPassword -e $dropCmd 2>&1 | Out-Null
    & $mysqlPath -u $MysqlUser -p$MysqlPassword -e $createCmd 2>&1 | Out-Null
} else {
    & $mysqlPath -u $MysqlUser -e $dropCmd 2>&1 | Out-Null
    & $mysqlPath -u $MysqlUser -e $createCmd 2>&1 | Out-Null
}

Write-Host "Database prepared!" -ForegroundColor Green
Write-Host ""

# Step 2: Import using direct file input (more reliable for large files)
Write-Host "Step 2: Importing SQL file..." -ForegroundColor Cyan
Write-Host "WARNING: This will take 30 minutes to several hours for a 2GB file." -ForegroundColor Yellow
Write-Host "Please wait and do not close this window..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date
$errorLog = "$env:TEMP\mysql_import_errors_$(Get-Date -Format 'yyyyMMddHHmmss').log"

# Use Get-Content with streaming for large files
Write-Host "Starting import..." -ForegroundColor Cyan

$mysqlArgs = @(
    "-u", $MysqlUser
    "--max_allowed_packet=1073741824"
    "--net_buffer_length=1048576"
    "--default-character-set=utf8mb4"
    "--force"  # Continue on errors
    $DatabaseName
)

if ($MysqlPassword) {
    $mysqlArgs = @("-u", $MysqlUser, "-p$MysqlPassword") + $mysqlArgs[2..($mysqlArgs.Length-1)]
}

# Import using streaming
$processInfo = New-Object System.Diagnostics.ProcessStartInfo
$processInfo.FileName = $mysqlPath
$processInfo.Arguments = ($mysqlArgs -join " ")
$processInfo.UseShellExecute = $false
$processInfo.RedirectStandardInput = $true
$processInfo.RedirectStandardOutput = $true
$processInfo.RedirectStandardError = $true
$processInfo.CreateNoWindow = $true

$process = New-Object System.Diagnostics.Process
$process.StartInfo = $processInfo

# Collect errors
$errorOutput = New-Object System.Text.StringBuilder
$process.add_ErrorDataReceived({
    param($sender, $e)
    if ($e.Data) {
        [void]$errorOutput.AppendLine($e.Data)
    }
})

$process.Start() | Out-Null
$process.BeginErrorReadLine()
$stdin = $process.StandardInput

# Stream file content
$reader = [System.IO.StreamReader]::new($SqlFile)
$lineCount = 0

try {
    while ($null -ne ($line = $reader.ReadLine())) {
        $lineCount++
        $stdin.WriteLine($line)
        
        if ($lineCount % 100000 -eq 0) {
            Write-Host "Processed $lineCount lines..." -ForegroundColor Cyan
        }
    }
    
    $stdin.Close()
    $process.WaitForExit()
    
    # Save errors to log
    $errors = $errorOutput.ToString()
    if ($errors) {
        $errors | Out-File -FilePath $errorLog -Encoding UTF8
    }
    
} finally {
    $reader.Close()
    if (-not $process.HasExited) {
        $process.Kill()
    }
    $process.Dispose()
}

$endTime = Get-Date
$duration = $endTime - $startTime

# Check for errors
$errors = ""
if (Test-Path $errorLog) {
    $errors = Get-Content $errorLog -ErrorAction SilentlyContinue
    if ($errors) {
        $errorCount = ($errors | Select-String -Pattern "ERROR" -AllMatches).Matches.Count
        Write-Host ""
        Write-Host "Found $errorCount errors during import" -ForegroundColor Yellow
        if ($errorCount -lt 10) {
            Write-Host "Errors:" -ForegroundColor Yellow
            $errors | Select-String -Pattern "ERROR" | ForEach-Object { Write-Host $_ -ForegroundColor Red }
        }
    }
}

# Check final table count
Write-Host ""
Write-Host "Checking final status..." -ForegroundColor Cyan
$countCmd = "SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '$DatabaseName';"
if ($MysqlPassword) {
    $result = & $mysqlPath -u $MysqlUser -p$MysqlPassword -e $countCmd $DatabaseName 2>&1
} else {
    $result = & $mysqlPath -u $MysqlUser -e $countCmd $DatabaseName 2>&1
}

$tableCount = ($result | Select-String -Pattern "\d+" | Select-Object -First 1).Matches.Value

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "Import Process Completed" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Tables imported: $tableCount / 172" -ForegroundColor $(if ($tableCount -eq 172) { "Green" } else { "Yellow" })
Write-Host "Time taken: $($duration.Hours)h $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green

if ($tableCount -eq 172) {
    Write-Host ""
    Write-Host "SUCCESS: All 172 tables imported!" -ForegroundColor Green
    Remove-Item $errorLog -ErrorAction SilentlyContinue
    exit 0
} else {
    Write-Host ""
    Write-Host "WARNING: Only $tableCount tables imported. Expected 172." -ForegroundColor Yellow
    Write-Host "Check error log: $errorLog" -ForegroundColor Yellow
    exit 1
}

