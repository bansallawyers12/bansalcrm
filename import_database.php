<?php
/**
 * PHP Script to Import Large MySQL Database
 * Database: bansalc_db2
 * File: C:\Users\user\Downloads\bansalc_db2
 * 
 * Usage: php import_database.php
 */

// Configuration
$dbName = "bansalc_db2";
$dbFile = "C:\\Users\\user\\Downloads\\bansalc_db2";
$mysqlHost = "127.0.0.1";
$mysqlPort = "3306";
$mysqlUser = "root";
$mysqlPassword = "";

// Try to read from .env file if it exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            switch ($name) {
                case 'DB_HOST':
                    $mysqlHost = trim($value, '"\''); break;
                case 'DB_PORT':
                    $mysqlPort = trim($value, '"\''); break;
                case 'DB_USERNAME':
                    $mysqlUser = trim($value, '"\''); break;
                case 'DB_PASSWORD':
                    $mysqlPassword = trim($value, '"\''); break;
            }
        }
    }
}

echo "========================================\n";
echo "MySQL Database Import Script\n";
echo "========================================\n\n";

// Find database file
$dbFilePath = $dbFile;
if (!file_exists($dbFilePath)) {
    $dbFilePath = $dbFile . ".sql";
    if (!file_exists($dbFilePath)) {
        $dbFilePath = $dbFile . ".sql.gz";
        if (!file_exists($dbFilePath)) {
            echo "ERROR: Database file not found at:\n";
            echo "  $dbFile\n";
            echo "  $dbFile.sql\n";
            echo "  $dbFile.sql.gz\n";
            exit(1);
        }
    }
}

echo "Database File: $dbFilePath\n";
$fileSize = filesize($dbFilePath) / (1024 * 1024 * 1024);
echo "File Size: " . number_format($fileSize, 2) . " GB\n\n";

// Check if file is compressed
$isCompressed = (pathinfo($dbFilePath, PATHINFO_EXTENSION) === 'gz');
if ($isCompressed) {
    echo "Detected compressed file (.gz). Decompressing...\n";
    echo "This may take a while for large files.\n\n";
    
    $inputFile = gzopen($dbFilePath, 'rb');
    $outputFile = fopen('temp_import.sql', 'wb');
    
    if (!$inputFile || !$outputFile) {
        echo "ERROR: Could not open files for decompression.\n";
        exit(1);
    }
    
    $bufferSize = 1024 * 1024; // 1MB buffer
    $bytesRead = 0;
    $totalBytes = 0;
    $fileSizeBytes = filesize($dbFilePath);
    
    while (!gzeof($inputFile)) {
        $buffer = gzread($inputFile, $bufferSize);
        if ($buffer === false) break;
        
        fwrite($outputFile, $buffer);
        $totalBytes += strlen($buffer);
        
        $progress = ($totalBytes / $fileSizeBytes) * 100;
        echo "\rDecompressing: " . number_format($progress, 2) . "%";
    }
    
    gzclose($inputFile);
    fclose($outputFile);
    echo "\nDecompression complete!\n\n";
    
    $dbFilePath = 'temp_import.sql';
}

// Connect to MySQL
echo "Connecting to MySQL...\n";
try {
    $pdo = new PDO(
        "mysql:host=$mysqlHost;port=$mysqlPort",
        $mysqlUser,
        $mysqlPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    echo "Connected successfully!\n\n";
} catch (PDOException $e) {
    echo "ERROR: Could not connect to MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// Create database if it doesn't exist
echo "Creating database '$dbName' if it doesn't exist...\n";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database ready!\n\n";
} catch (PDOException $e) {
    echo "Warning: " . $e->getMessage() . "\n";
}

// Select the database
try {
    $pdo->exec("USE `$dbName`");
} catch (PDOException $e) {
    echo "ERROR: Could not select database: " . $e->getMessage() . "\n";
    exit(1);
}

// Import the SQL file
echo "Starting database import...\n";
echo "This may take a while for a 2GB database. Please be patient...\n\n";

$startTime = microtime(true);

// Read and execute SQL file in chunks
$handle = fopen($dbFilePath, 'r');
if (!$handle) {
    echo "ERROR: Could not open SQL file.\n";
    exit(1);
}

$query = '';
$queryCount = 0;
$bufferSize = 1024 * 1024; // 1MB buffer
$fileSizeBytes = filesize($dbFilePath);
$bytesRead = 0;

echo "Importing...\n";

while (!feof($handle)) {
    $chunk = fread($handle, $bufferSize);
    if ($chunk === false) break;
    
    $bytesRead += strlen($chunk);
    $query .= $chunk;
    
    // Process complete statements
    while (preg_match('/;\s*$/m', $query, $matches, PREG_OFFSET_CAPTURE)) {
        $statement = substr($query, 0, $matches[0][1] + 1);
        $query = substr($query, $matches[0][1] + 1);
        
        // Skip empty statements and comments
        $statement = trim($statement);
        if (empty($statement) || preg_match('/^--/', $statement) || preg_match('/^\/\*/', $statement)) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $queryCount++;
            
            if ($queryCount % 100 == 0) {
                $progress = ($bytesRead / $fileSizeBytes) * 100;
                echo "\rProgress: " . number_format($progress, 2) . "% | Queries executed: $queryCount";
            }
        } catch (PDOException $e) {
            // Skip some common errors that don't stop import
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "\nWarning: " . substr($e->getMessage(), 0, 100) . "...\n";
            }
        }
    }
    
    // Show progress
    $progress = ($bytesRead / $fileSizeBytes) * 100;
    echo "\rProgress: " . number_format($progress, 2) . "% | Queries executed: $queryCount";
}

// Execute any remaining query
if (!empty(trim($query))) {
    try {
        $pdo->exec($query);
        $queryCount++;
    } catch (PDOException $e) {
        echo "\nWarning: " . substr($e->getMessage(), 0, 100) . "...\n";
    }
}

fclose($handle);

$endTime = microtime(true);
$duration = $endTime - $startTime;
$hours = floor($duration / 3600);
$minutes = floor(($duration % 3600) / 60);
$seconds = $duration % 60;

echo "\n\n";
echo "========================================\n";
echo "Import completed successfully!\n";
echo "Time taken: {$hours}h {$minutes}m " . number_format($seconds, 2) . "s\n";
echo "Total queries executed: $queryCount\n";
echo "========================================\n";

// Clean up temporary file if created
if ($isCompressed && file_exists('temp_import.sql')) {
    unlink('temp_import.sql');
    echo "Cleaned up temporary files.\n";
}

echo "\n";
