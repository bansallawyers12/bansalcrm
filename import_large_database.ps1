# PowerShell script to import large SQL file (2GB) into MySQL database
# Usage: .\import_large_database.ps1

$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$sqlFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql"
$databaseName = "bansalc_db2"
$mysqlUser = "root"
$mysqlPassword = ""  # Default XAMPP root password is empty

Write-Host "Starting database import process..." -ForegroundColor Green
Write-Host "Database: $databaseName" -ForegroundColor Yellow
Write-Host "SQL File: $sqlFile" -ForegroundColor Yellow
Write-Host "File Size: $([math]::Round((Get-Item $sqlFile).Length / 1GB, 2)) GB" -ForegroundColor Yellow

# Check if SQL file exists
if (-not (Test-Path $sqlFile)) {
    Write-Host "Error: SQL file not found at $sqlFile" -ForegroundColor Red
    exit 1
}

# Check if MySQL is available
if (-not (Test-Path $mysqlPath)) {
    Write-Host "Error: MySQL not found at $mysqlPath" -ForegroundColor Red
    exit 1
}

Write-Host "`nStep 1: Creating database if it doesn't exist..." -ForegroundColor Cyan
$createDbCmd = "CREATE DATABASE IF NOT EXISTS `"$databaseName`" CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($mysqlPassword) {
    & $mysqlPath -u $mysqlUser -p$mysqlPassword -e $createDbCmd
} else {
    & $mysqlPath -u $mysqlUser -e $createDbCmd
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error creating database. Please check MySQL credentials." -ForegroundColor Red
    exit 1
}

Write-Host "Database created/verified successfully!" -ForegroundColor Green

Write-Host "`nStep 2: Importing SQL file (this may take a while for 2GB file)..." -ForegroundColor Cyan
Write-Host "Please wait... This process can take 30 minutes to several hours depending on your system." -ForegroundColor Yellow

# Import with increased settings for large files
# max_allowed_packet increased to 1GB, other settings optimized for large imports
$importSettings = @"
SET GLOBAL max_allowed_packet=1073741824;
SET GLOBAL net_buffer_length=1048576;
SET GLOBAL interactive_timeout=28800;
SET GLOBAL wait_timeout=28800;
"@

# Save settings to temp file
$tempSettingsFile = "$env:TEMP\mysql_import_settings.sql"
$importSettings | Out-File -FilePath $tempSettingsFile -Encoding ASCII

# Import the database using Get-Content for large files
$startTime = Get-Date
Write-Host "Importing data... Please be patient, this may take a long time." -ForegroundColor Yellow

if ($mysqlPassword) {
    Get-Content $sqlFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser -p$mysqlPassword --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName
} else {
    Get-Content $sqlFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName
}

$endTime = Get-Date
$duration = $endTime - $startTime

if ($LASTEXITCODE -eq 0) {
    Write-Host "`nImport completed successfully!" -ForegroundColor Green
    Write-Host "Time taken: $($duration.Hours) hours, $($duration.Minutes) minutes, $($duration.Seconds) seconds" -ForegroundColor Green
} else {
    Write-Host "`nImport failed with error code: $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Please check the error messages above." -ForegroundColor Red
    exit 1
}

# Clean up temp file
Remove-Item $tempSettingsFile -ErrorAction SilentlyContinue

Write-Host "`nDatabase import process completed!" -ForegroundColor Green

