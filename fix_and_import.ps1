# PowerShell script to fix SQL file and import in one go
# This processes the SQL file and adds ROW_FORMAT=DYNAMIC to CREATE TABLE statements

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql"
$databaseName = "bansalc_db2"
$mysqlUser = "root"
$mysqlPassword = ""

Write-Host "========================================" -ForegroundColor Green
Write-Host "Database Import with ROW_FORMAT Fix" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Database: $databaseName" -ForegroundColor Yellow
Write-Host "SQL File: $sqlFile" -ForegroundColor Yellow
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

# Step 1: Create database
Write-Host "Step 1: Creating database..." -ForegroundColor Cyan
$createDbCmd = "CREATE DATABASE IF NOT EXISTS `"$databaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($mysqlPassword) {
    & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $createDbCmd 2>&1 | Out-Null
} else {
    & $mysqlPath -u $mysqlUser -e $createDbCmd 2>&1 | Out-Null
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error creating database!" -ForegroundColor Red
    exit 1
}
Write-Host "Database created successfully!" -ForegroundColor Green
Write-Host ""

# Step 2: Import with on-the-fly ROW_FORMAT fix
Write-Host "Step 2: Importing SQL file (fixing ROW_FORMAT on-the-fly)..." -ForegroundColor Cyan
Write-Host "WARNING: This may take 30 minutes to several hours for a 2GB file." -ForegroundColor Yellow
Write-Host "Please wait and do not close this window..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date
$lineCount = 0
$fixedCount = 0

# Open MySQL process for import
$mysqlArgs = @(
    "-u", $mysqlUser
    "--max_allowed_packet=1073741824"
    "--net_buffer_length=1048576"
    "--default-character-set=utf8mb4"
    $databaseName
)

if ($mysqlPassword) {
    $mysqlArgs = @("-u", $mysqlUser, "-p$mysqlPassword", "--max_allowed_packet=1073741824", "--net_buffer_length=1048576", "--default-character-set=utf8mb4", $databaseName)
}

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

# Start error output collection
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

# Read and process SQL file
$reader = [System.IO.StreamReader]::new($sqlFile)
$currentTable = ""
$inCreateTable = $false
$tableLines = New-Object System.Collections.ArrayList

try {
    while ($null -ne ($line = $reader.ReadLine())) {
        $lineCount++
        
        # Detect CREATE TABLE statement
        if ($line -match "^\s*CREATE\s+TABLE" -and -not $inCreateTable) {
            $inCreateTable = $true
            $currentTable = $line
            $tableLines.Clear() | Out-Null
            $tableLines.Add($line) | Out-Null
        }
        elseif ($inCreateTable) {
            $tableLines.Add($line) | Out-Null
            $currentTable += "`n" + $line
            
            # Check if this is the end of CREATE TABLE (ENGINE= or closing with semicolon)
            if ($line -match "ENGINE\s*=" -or ($line -match "\)\s*;" -and $line -notmatch "ROW_FORMAT")) {
                # Check if ROW_FORMAT is missing
                if ($currentTable -notmatch "ROW_FORMAT") {
                    $fixedCount++
                    # Add ROW_FORMAT=DYNAMIC before ENGINE or before semicolon
                    if ($line -match "ENGINE\s*=") {
                        $line = $line -replace "(ENGINE\s*=)", "ROW_FORMAT=DYNAMIC `$1"
                        $tableLines[$tableLines.Count - 1] = $line
                    }
                    elseif ($line -match "\)\s*;") {
                        $line = $line -replace "(\)\s*;)", ") ROW_FORMAT=DYNAMIC;"
                        $tableLines[$tableLines.Count - 1] = $line
                    }
                }
                
                # Write the fixed CREATE TABLE statement
                foreach ($tableLine in $tableLines) {
                    $stdin.WriteLine($tableLine)
                }
                
                $inCreateTable = $false
                $currentTable = ""
                $tableLines.Clear() | Out-Null
            }
        }
        else {
            # Regular line, write as-is
            $stdin.WriteLine($line)
        }
        
        # Progress indicator
        if ($lineCount % 50000 -eq 0) {
            Write-Host "Processed $lineCount lines, fixed $fixedCount tables..." -ForegroundColor Cyan
        }
    }
    
    # Close stdin
    $stdin.Close()
    
    # Wait for process to complete
    $process.WaitForExit()
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    # Get any error output
    $errors = $errorOutput.ToString()
    
    if ($process.ExitCode -eq 0) {
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "Import completed successfully!" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "Total lines processed: $lineCount" -ForegroundColor Green
        Write-Host "Tables fixed: $fixedCount" -ForegroundColor Green
        Write-Host "Time taken: $($duration.Hours)h $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "========================================" -ForegroundColor Red
        Write-Host "Import failed!" -ForegroundColor Red
        Write-Host "========================================" -ForegroundColor Red
        Write-Host "Exit code: $($process.ExitCode)" -ForegroundColor Red
        if ($errors) {
            Write-Host "Errors:" -ForegroundColor Red
            Write-Host $errors -ForegroundColor Red
        }
        exit 1
    }
    
} finally {
    $reader.Close()
    if (-not $process.HasExited) {
        $process.Kill()
    }
    $process.Dispose()
}

