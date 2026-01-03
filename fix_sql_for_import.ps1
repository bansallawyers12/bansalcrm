# PowerShell script to fix SQL file for large row imports
# Adds ROW_FORMAT=DYNAMIC to CREATE TABLE statements

param(
    [string]$InputFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025.sql",
    [string]$OutputFile = "C:\xampp\htdocs\bansalcrm\bansalc_db229112025_fixed.sql"
)

Write-Host "Fixing SQL file for large row imports..." -ForegroundColor Green
Write-Host "Input: $InputFile" -ForegroundColor Yellow
Write-Host "Output: $OutputFile" -ForegroundColor Yellow

if (-not (Test-Path $InputFile)) {
    Write-Host "Error: Input file not found!" -ForegroundColor Red
    exit 1
}

$reader = [System.IO.StreamReader]::new($InputFile)
$writer = [System.IO.StreamWriter]::new($OutputFile)
$lineCount = 0
$modifiedCount = 0

try {
    while ($null -ne ($line = $reader.ReadLine())) {
        $lineCount++
        
        # Check if line contains CREATE TABLE and doesn't already have ROW_FORMAT
        if ($line -match "CREATE TABLE" -and $line -notmatch "ROW_FORMAT") {
            # Add ROW_FORMAT=DYNAMIC before ENGINE or at the end if no ENGINE
            if ($line -match "ENGINE\s*=") {
                $line = $line -replace "(ENGINE\s*=)", "ROW_FORMAT=DYNAMIC `$1"
            } elseif ($line -match "\)\s*;") {
                $line = $line -replace "(\)\s*;)", ") ROW_FORMAT=DYNAMIC;"
            } elseif ($line -match "\)\s*$") {
                $line = $line -replace "(\)\s*)$", ") ROW_FORMAT=DYNAMIC"
            }
            $modifiedCount++
        }
        # Also check for ALTER TABLE statements that might need it
        elseif ($line -match "ALTER TABLE.*ADD" -and $line -notmatch "ROW_FORMAT") {
            # For ALTER TABLE, we'll add it at the end if there's a semicolon
            if ($line -match ";\s*$") {
                $line = $line -replace "(;\s*)$", " ROW_FORMAT=DYNAMIC;"
                $modifiedCount++
            }
        }
        
        $writer.WriteLine($line)
        
        # Progress indicator every 100k lines
        if ($lineCount % 100000 -eq 0) {
            Write-Host "Processed $lineCount lines, modified $modifiedCount tables..." -ForegroundColor Cyan
        }
    }
    
    Write-Host "`nCompleted!" -ForegroundColor Green
    Write-Host "Total lines processed: $lineCount" -ForegroundColor Green
    Write-Host "Tables modified: $modifiedCount" -ForegroundColor Green
    Write-Host "Fixed file saved to: $OutputFile" -ForegroundColor Green
    
} finally {
    $reader.Close()
    $writer.Close()
}

