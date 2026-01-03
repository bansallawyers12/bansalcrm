# Script to fix leads.sql by converting some VARCHAR columns to TEXT to reduce row size

$sqlFile = "C:\xampp\htdocs\bansalcrm\leads.sql"
$fixedFile = "C:\xampp\htdocs\bansalcrm\leads_fixed.sql"

Write-Host "Fixing leads.sql table structure..." -ForegroundColor Green
Write-Host "Converting some VARCHAR(255) columns to TEXT to reduce row size..." -ForegroundColor Yellow

$content = Get-Content $sqlFile -Raw -Encoding UTF8

# Convert some VARCHAR(255) columns that are less likely to be indexed to TEXT
# These are typically comment/note fields and longer text fields
$replacements = @{
    "`"comments_note`" text DEFAULT NULL" = "`"comments_note`" text DEFAULT NULL"  # Already TEXT
    "`"related_files`" text DEFAULT NULL" = "`"related_files`" text DEFAULT NULL"  # Already TEXT
    "`"advertisements_name`" varchar(255)" = "`"advertisements_name`" text"
    "`"service`" varchar(255)" = "`"service`" text"
    "`"social_link`" varchar(255)" = "`"social_link`" text"
}

# Apply replacements
foreach ($key in $replacements.Keys) {
    $content = $content -replace [regex]::Escape($key), $replacements[$key]
}

# Ensure ROW_FORMAT=DYNAMIC is in the CREATE TABLE
if ($content -notmatch "ROW_FORMAT=DYNAMIC") {
    $content = $content -replace "(\)\s+ENGINE=InnoDB)", ") ROW_FORMAT=DYNAMIC ENGINE=InnoDB"
}

# Write fixed file
$content | Out-File -FilePath $fixedFile -Encoding UTF8 -NoNewline

Write-Host "Fixed file created: $fixedFile" -ForegroundColor Green
Write-Host "Now importing the fixed file..." -ForegroundColor Yellow
Write-Host ""

# Import the fixed file
$mysqlPath = "C:\xampp\mysql\bin\mysql.exe"
$databaseName = "bansalc_db2"
$mysqlUser = "root"
$mysqlPassword = ""

# Drop leads table
$dropCmd = "DROP TABLE IF EXISTS `"$databaseName`".`leads`;"
& $mysqlPath -u $mysqlUser -e $dropCmd 2>&1 | Out-Null

# Import
Get-Content $fixedFile -Encoding UTF8 | & $mysqlPath -u $mysqlUser --max_allowed_packet=1073741824 --net_buffer_length=1048576 --default-character-set=utf8mb4 $databaseName

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Import completed successfully!" -ForegroundColor Green
    Write-Host "You can delete the fixed file: $fixedFile" -ForegroundColor Yellow
} else {
    Write-Host ""
    Write-Host "Import failed. Exit code: $LASTEXITCODE" -ForegroundColor Red
}

