# PowerShell script to import large SQL file with automatic ROW_FORMAT fix
# This streams the file and fixes CREATE TABLE statements on-the-fly

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql"
$databaseName = "bansalc_db2"
$mysqlUser = "root"
$mysqlPassword = ""

Write-Host "Starting database import with ROW_FORMAT fix..." -ForegroundColor Green
Write-Host "Database: $databaseName" -ForegroundColor Yellow
Write-Host "SQL File: $sqlFile" -ForegroundColor Yellow

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

# Step 1: Create database
Write-Host "`nStep 1: Creating database..." -ForegroundColor Cyan
$createDbCmd = "CREATE DATABASE IF NOT EXISTS `"$databaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($mysqlPassword) {
    & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $createDbCmd | Out-Null
} else {
    & $mysqlPath -u $mysqlUser -e $createDbCmd | Out-Null
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error creating database!" -ForegroundColor Red
    exit 1
}

# Step 2: Set MySQL global settings
Write-Host "Step 2: Configuring MySQL for large rows..." -ForegroundColor Cyan
$configCmd = "SET GLOBAL innodb_default_row_format='DYNAMIC'; SET GLOBAL innodb_file_format='Barracuda'; SET GLOBAL innodb_large_prefix=1;"
if ($mysqlPassword) {
    & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $configCmd | Out-Null
} else {
    & $mysqlPath -u $mysqlUser -e $configCmd | Out-Null
}

# Step 3: Import with streaming fix
Write-Host "Step 3: Importing SQL file (fixing ROW_FORMAT on-the-fly)..." -ForegroundColor Cyan
Write-Host "This may take a very long time for a 2GB file. Please wait..." -ForegroundColor Yellow

$startTime = Get-Date
$lineCount = 0
$buffer = New-Object System.Text.StringBuilder
$inCreateTable = $false
$createTableBuffer = New-Object System.Text.StringBuilder

# Function to fix CREATE TABLE statement
function Fix-CreateTable {
    param([string]$line)
    
    if ($line -match "CREATE TABLE") {
        $inCreateTable = $true
        $createTableBuffer.Clear() | Out-Null
        $createTableBuffer.Append($line) | Out-Null
        return $null
    }
    
    if ($inCreateTable) {
        $createTableBuffer.Append("`n" + $line) | Out-Null
        
        # Check if this is the end of CREATE TABLE (has ENGINE or closing parenthesis with semicolon)
        if ($line -match "ENGINE\s*=" -or ($line -match "\)\s*;" -and $line -notmatch "ROW_FORMAT")) {
            $tableDef = $createTableBuffer.ToString()
            $inCreateTable = $false
            
            # Add ROW_FORMAT=DYNAMIC if not present
            if ($tableDef -notmatch "ROW_FORMAT") {
                if ($tableDef -match "ENGINE\s*=") {
                    $tableDef = $tableDef -replace "(ENGINE\s*=)", "ROW_FORMAT=DYNAMIC `$1"
                } elseif ($tableDef -match "\)\s*;") {
                    $tableDef = $tableDef -replace "(\)\s*;)", ") ROW_FORMAT=DYNAMIC;"
                }
            }
            
            return $tableDef
        }
        
        return $null
    }
    
    return $line
}

# Use a simpler approach: read file in chunks and fix CREATE TABLE statements
$reader = [System.IO.StreamReader]::new($sqlFile)
$process = Start-Process -FilePath $mysqlPath -ArgumentList @(
    "-u", $mysqlUser,
    "--max_allowed_packet=1073741824",
    "--net_buffer_length=1048576",
    "--default-character-set=utf8mb4",
    "--innodb_file_format=Barracuda",
    "--innodb_large_prefix=1",
    $databaseName
) -NoNewWindow -PassThru -RedirectStandardInput $true

$stdin = $process.StandardInput

try {
    $currentTable = ""
    $inTable = $false
    
    while ($null -ne ($line = $reader.ReadLine())) {
        $lineCount++
        
        if ($line -match "^CREATE TABLE" -and $line -notmatch "ROW_FORMAT") {
            $inTable = $true
            $currentTable = $line
        }
        elseif ($inTable) {
            $currentTable += "`n" + $line
            
            # Check if this is the end of CREATE TABLE
            if ($line -match "ENGINE\s*=" -or ($line -match "\)\s*;" -and $line -notmatch "ROW_FORMAT")) {
                # Fix the CREATE TABLE statement
                if ($currentTable -notmatch "ROW_FORMAT") {
                    if ($currentTable -match "ENGINE\s*=") {
                        $currentTable = $currentTable -replace "(ENGINE\s*=)", "ROW_FORMAT=DYNAMIC `$1"
                    } elseif ($currentTable -match "\)\s*;") {
                        $currentTable = $currentTable -replace "(\)\s*;)", ") ROW_FORMAT=DYNAMIC;"
                    }
                }
                
                $stdin.WriteLine($currentTable)
                $inTable = $false
                $currentTable = ""
            }
        }
        else {
            $stdin.WriteLine($line)
        }
        
        if ($lineCount % 100000 -eq 0) {
            Write-Host "Processed $lineCount lines..." -ForegroundColor Cyan
        }
    }
    
    $stdin.Close()
    $process.WaitForExit()
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    if ($process.ExitCode -eq 0) {
        Write-Host "`nImport completed successfully!" -ForegroundColor Green
        Write-Host "Time taken: $($duration.Hours)h $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
    } else {
        Write-Host "`nImport failed with exit code: $($process.ExitCode)" -ForegroundColor Red
        exit 1
    }
    
} finally {
    $reader.Close()
    if (-not $stdin.BaseStream.CanWrite) {
        $stdin.Dispose()
    }
}

