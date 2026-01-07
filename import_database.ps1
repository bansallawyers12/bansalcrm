# PowerShell Script to Import Large MySQL Database
# Database: bansalc_db2
# File: C:\Users\user\Downloads\bansalc_db2

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "MySQL Database Import Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$dbName = "bansalc_db2"
$dbFile = "C:\Users\user\Downloads\bansalc_db2\bansalc_db2"
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$mysqlUser = "root"
$mysqlPassword = ""

# Check if MySQL path exists, if not try common locations
if (-not (Test-Path $mysqlPath)) {
    $mysqlPath = "mysql.exe"
    Write-Host "Using system PATH for mysql.exe" -ForegroundColor Yellow
}

# Check if database file exists
$dbFilePath = $dbFile
if (-not (Test-Path $dbFilePath)) {
    # Try with .sql extension
    $dbFilePath = "$dbFile.sql"
    if (-not (Test-Path $dbFilePath)) {
        # Try with .sql.gz extension
        $dbFilePath = "$dbFile.sql.gz"
        if (-not (Test-Path $dbFilePath)) {
            Write-Host "ERROR: Database file not found at:" -ForegroundColor Red
            Write-Host "  $dbFile" -ForegroundColor Red
            Write-Host "  $dbFile.sql" -ForegroundColor Red
            Write-Host "  $dbFile.sql.gz" -ForegroundColor Red
            exit 1
        }
    }
}

Write-Host "Database File: $dbFilePath" -ForegroundColor Green
$fileSize = (Get-Item $dbFilePath).Length / 1GB
Write-Host "File Size: $([math]::Round($fileSize, 2)) GB" -ForegroundColor Green
Write-Host ""

# Get MySQL password from environment variable or use empty
if ([string]::IsNullOrEmpty($mysqlPassword)) {
    $mysqlPassword = $env:MYSQL_PASSWORD
    if ([string]::IsNullOrEmpty($mysqlPassword)) {
        Write-Host "Using empty password. Set MYSQL_PASSWORD environment variable if password is required." -ForegroundColor Yellow
    }
}

# Build MySQL command
$mysqlArgs = @()

# Check if file is compressed
if ($dbFilePath -like "*.gz") {
    Write-Host "Detected compressed file (.gz). Decompressing and importing..." -ForegroundColor Yellow
    Write-Host ""
    
    # Use gunzip and pipe to mysql
    $gunzipPath = "C:\xampp\mysql\bin\gunzip.exe"
    if (-not (Test-Path $gunzipPath)) {
        $gunzipPath = "gunzip.exe"
    }
    
    # Check if gunzip is available, if not, use PowerShell decompression
    try {
        $null = Get-Command gunzip.exe -ErrorAction Stop
        Write-Host "Using gunzip for decompression..." -ForegroundColor Green
        $process = Start-Process -FilePath $gunzipPath -ArgumentList "-c", "`"$dbFilePath`"" -NoNewWindow -PassThru -RedirectStandardOutput "temp_import.sql" -Wait
        $dbFilePath = "temp_import.sql"
    } catch {
        Write-Host "gunzip not found. Using PowerShell decompression (slower)..." -ForegroundColor Yellow
        Write-Host "Decompressing file... This may take a while for large files." -ForegroundColor Yellow
        
        $inputStream = New-Object System.IO.FileStream($dbFilePath, [System.IO.FileMode]::Open, [System.IO.FileAccess]::Read)
        $gzipStream = New-Object System.IO.Compression.GZipStream($inputStream, [System.IO.Compression.CompressionMode]::Decompress)
        $outputStream = New-Object System.IO.FileStream("temp_import.sql", [System.IO.FileMode]::Create, [System.IO.FileAccess]::Write)
        
        $buffer = New-Object byte[](1024 * 1024) # 1MB buffer
        $bytesRead = 0
        $totalBytes = 0
        
        while (($bytesRead = $gzipStream.Read($buffer, 0, $buffer.Length)) -gt 0) {
            $outputStream.Write($buffer, 0, $bytesRead)
            $totalBytes += $bytesRead
            $progress = ($totalBytes / $inputStream.Length) * 100
            Write-Progress -Activity "Decompressing" -Status "Progress: $([math]::Round($progress, 2))%" -PercentComplete $progress
        }
        
        $gzipStream.Close()
        $inputStream.Close()
        $outputStream.Close()
        Write-Progress -Activity "Decompressing" -Completed
        
        $dbFilePath = "temp_import.sql"
        Write-Host "Decompression complete!" -ForegroundColor Green
        Write-Host ""
    }
}

# Drop and recreate database to ensure clean import
Write-Host "Preparing database '$dbName'..." -ForegroundColor Yellow
$dropDbArgs = @()
if (-not [string]::IsNullOrEmpty($mysqlPassword)) {
    $dropDbArgs = @("-u", $mysqlUser, "-p$mysqlPassword", "-e", "DROP DATABASE IF EXISTS `"$dbName`"; CREATE DATABASE `"$dbName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
} else {
    $dropDbArgs = @("-u", $mysqlUser, "-e", "DROP DATABASE IF EXISTS `"$dbName`"; CREATE DATABASE `"$dbName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
}

try {
    & $mysqlPath $dropDbArgs 2>&1 | Out-Null
    Write-Host "Database ready (dropped and recreated)!" -ForegroundColor Green
} catch {
    Write-Host "Warning: Could not recreate database" -ForegroundColor Yellow
}

# Set MySQL session variables to handle large rows
Write-Host "Configuring MySQL session for large rows..." -ForegroundColor Yellow
$configArgs = @()
if (-not [string]::IsNullOrEmpty($mysqlPassword)) {
    $configArgs = @("-u", $mysqlUser, "-p$mysqlPassword", "-e", "SET GLOBAL innodb_default_row_format='DYNAMIC'; SET SESSION sql_mode='';")
} else {
    $configArgs = @("-u", $mysqlUser, "-e", "SET GLOBAL innodb_default_row_format='DYNAMIC'; SET SESSION sql_mode='';")
}

try {
    & $mysqlPath $configArgs 2>&1 | Out-Null
} catch {
    Write-Host "Warning: Could not set global variables (may require admin privileges)" -ForegroundColor Yellow
}
Write-Host ""

# Prepare import command with increased settings for large files
Write-Host "Starting database import..." -ForegroundColor Yellow
Write-Host "This may take a while for a 2GB database. Please be patient..." -ForegroundColor Yellow
Write-Host ""

$startTime = Get-Date

# Build mysql import command with optimized settings for large files
$importArgs = @()
if (-not [string]::IsNullOrEmpty($mysqlPassword)) {
    $importArgs = @(
        "-u", $mysqlUser,
        "-p$mysqlPassword",
        "--max_allowed_packet=1G",
        "--net_buffer_length=16K",
        "--default-character-set=utf8mb4",
        $dbName
    )
} else {
    $importArgs = @(
        "-u", $mysqlUser,
        "--max_allowed_packet=1G",
        "--net_buffer_length=16K",
        "--default-character-set=utf8mb4",
        $dbName
    )
}

# Import the database using cmd.exe redirection (handles large files efficiently)
try {
    # Build the command string for cmd.exe
    $mysqlCmd = "`"$mysqlPath`""
    $mysqlCmd += " -u `"$mysqlUser`""
    if (-not [string]::IsNullOrEmpty($mysqlPassword)) {
        $mysqlCmd += " -p`"$mysqlPassword`""
    }
    $mysqlCmd += " --max_allowed_packet=1G"
    $mysqlCmd += " --net_buffer_length=16K"
    $mysqlCmd += " --default-character-set=utf8mb4"
    $mysqlCmd += " --force"
    $mysqlCmd += " --init-command=`"SET SESSION sql_mode=''; SET SESSION innodb_strict_mode=0;`""
    $mysqlCmd += " `"$dbName`""
    $mysqlCmd += " < `"$dbFilePath`""
    
    # Use cmd.exe to handle the redirection properly for large files
    $result = cmd /c $mysqlCmd 2>&1
    $exitCode = $LASTEXITCODE
    
    if ($exitCode -ne 0) {
        Write-Host "MySQL output:" -ForegroundColor Yellow
        Write-Host $result -ForegroundColor Yellow
        throw "MySQL import process exited with code $exitCode"
    }
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Import completed successfully!" -ForegroundColor Green
    Write-Host "Time taken: $($duration.Hours)h $($duration.Minutes)m $($duration.Seconds)s" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    
    # Clean up temporary file if created
    if (Test-Path "temp_import.sql") {
        Remove-Item "temp_import.sql" -Force
        Write-Host "Cleaned up temporary files." -ForegroundColor Green
    }
    
} catch {
    Write-Host ""
    Write-Host "ERROR: Import failed!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    Write-Host ""
    Write-Host "Troubleshooting tips:" -ForegroundColor Yellow
    Write-Host "1. Check MySQL is running" -ForegroundColor Yellow
    Write-Host "2. Verify database credentials" -ForegroundColor Yellow
    Write-Host "3. Check MySQL max_allowed_packet setting (should be at least 1G)" -ForegroundColor Yellow
    Write-Host "4. Ensure you have enough disk space" -ForegroundColor Yellow
    Write-Host "5. Check MySQL error log for details" -ForegroundColor Yellow
    
    # Clean up temporary file if created
    if (Test-Path "temp_import.sql") {
        Remove-Item "temp_import.sql" -Force
    }
    
    exit 1
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
