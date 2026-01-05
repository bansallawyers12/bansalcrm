<?php
/**
 * Script to find database tables that are not currently being used in the codebase
 * Run this from command line: php check_unused_tables.php
 * 
 * Make sure to update database credentials below before running
 */

// Read database config from .env file
function getEnvValue($key, $default = '') {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue; // Skip comments
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                if ($name === $key) {
                    // Remove quotes if present
                    return trim($value, '"\'');
                }
            }
        }
    }
    return $default;
}

// Get database credentials
$host = getEnvValue('DB_HOST', '127.0.0.1');
$dbname = getEnvValue('DB_DATABASE', '');
$username = getEnvValue('DB_USERNAME', 'root');
$password = getEnvValue('DB_PASSWORD', '');

// If .env not found or DB_DATABASE is empty, use defaults
if (empty($dbname)) {
    echo "⚠️  Warning: Could not read DB_DATABASE from .env file.\n";
    echo "Please update database credentials in this script or create a .env file.\n\n";
    // Fallback: Manual database connection - UPDATE THESE WITH YOUR CREDENTIALS
    $host = '127.0.0.1';
    $dbname = 'bansalcrm'; // Common default - UPDATE THIS if needed
    $username = 'root'; // UPDATE THIS
    $password = ''; // UPDATE THIS
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Unused Database Tables Analysis ===\n\n";
    
    // Get all tables from database
    echo "Fetching all tables from database...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = [];
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $allTables[] = $row[0];
    }
    
    echo "Found " . count($allTables) . " tables in database\n\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Laravel system tables to exclude from analysis
    $systemTables = [
        'migrations',
        'password_resets',
        'personal_access_tokens',
        'sessions',
        'jobs',
        'failed_jobs',
        'cache',
        'cache_locks',
    ];
    
    // Get project root directory
    $projectRoot = __DIR__;
    
    // Analyze each table
    $unusedTables = [];
    $usedTables = [];
    
    foreach ($allTables as $table) {
        // Skip system tables
        if (in_array($table, $systemTables)) {
            echo "⏭️  Skipping system table: $table\n";
            continue;
        }
        
        echo "Checking table: $table\n";
        echo str_repeat("-", 80) . "\n";
        
        $isUsed = false;
        $references = [];
        
        // 1. Check for Model files
        // Convert table name to possible model names
        // e.g., "clients" -> "Client", "activities_logs" -> "ActivitiesLog"
        $modelNameVariations = [];
        
        // Remove underscores and convert to PascalCase, then try singular
        $parts = explode('_', $table);
        $pascalCase = '';
        foreach ($parts as $part) {
            $pascalCase .= ucfirst($part);
        }
        $modelNameVariations[] = rtrim($pascalCase, 's'); // Remove trailing 's' for plural
        $modelNameVariations[] = $pascalCase; // Keep as is
        
        // Also try the table name directly
        $modelNameVariations[] = ucfirst($table);
        
        // Check all model files for matches
        $modelDir = $projectRoot . "/app/Models/";
        if (is_dir($modelDir)) {
            $modelFiles = glob($modelDir . "*.php");
            foreach ($modelFiles as $modelFile) {
                $modelBaseName = basename($modelFile, '.php');
                if (in_array($modelBaseName, $modelNameVariations)) {
                    $isUsed = true;
                    $references[] = "Model: " . basename($modelFile);
                    break;
                }
            }
        }
        
        // 2. Search for table name in codebase using more specific patterns
        // Search in PHP files
        $phpFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($projectRoot)
        );
        
        foreach ($phpFiles as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                
                // Skip vendor directory
                if (strpos($filePath, '/vendor/') !== false || strpos($filePath, '\\vendor\\') !== false) {
                    continue;
                }
                
                // Skip this script
                if (basename($filePath) === 'check_unused_tables.php') {
                    continue;
                }
                
                $content = file_get_contents($filePath);
                
                // More specific patterns to avoid false positives
                $patterns = [
                    "/DB::table\(['\"]" . preg_quote($table, '/') . "['\"]/i",
                    "/DB::table\(`" . preg_quote($table, '/') . "`\)/i",
                    "/FROM\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i",
                    "/JOIN\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i",
                    "/INTO\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i",
                    "/UPDATE\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i",
                    "/DELETE\s+FROM\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i",
                    "/Schema::(create|table|drop)\(['\"]" . preg_quote($table, '/') . "['\"]/i",
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $isUsed = true;
                        $relativePath = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $filePath);
                        $references[] = "Code: $relativePath";
                        break 2; // Break both loops
                    }
                }
            }
        }
        
        // 3. Check migrations
        $migrationFiles = glob($projectRoot . "/database/migrations/*.php");
        foreach ($migrationFiles as $migrationFile) {
            $content = file_get_contents($migrationFile);
            if (stripos($content, $table) !== false) {
                $isUsed = true;
                $references[] = "Migration: " . basename($migrationFile);
                break;
            }
        }
        
        // 4. Check for table in protected $table property in models
        $modelDir = $projectRoot . "/app/Models/";
        if (is_dir($modelDir)) {
            $modelFiles = glob($modelDir . "*.php");
            foreach ($modelFiles as $modelFile) {
                $content = file_get_contents($modelFile);
                if (preg_match("/protected\s+\\\$table\s*=\s*['\"]" . preg_quote($table, '/') . "['\"]/", $content)) {
                    $isUsed = true;
                    $references[] = "Model \$table property: " . basename($modelFile);
                    break;
                }
            }
        }
        
        // Display results
        if ($isUsed) {
            echo "✓ Table IS USED\n";
            echo "  References found:\n";
            foreach (array_unique($references) as $ref) {
                echo "    - $ref\n";
            }
            $usedTables[$table] = $references;
        } else {
            echo "❌ Table NOT USED\n";
            $unusedTables[] = $table;
        }
        
        echo "\n";
    }
    
    // Summary
    echo str_repeat("=", 80) . "\n";
    echo "SUMMARY\n";
    echo str_repeat("=", 80) . "\n\n";
    
    echo "Total tables analyzed: " . (count($allTables) - count($systemTables)) . "\n";
    echo "Used tables: " . count($usedTables) . "\n";
    echo "Unused tables: " . count($unusedTables) . "\n\n";
    
    if (count($unusedTables) > 0) {
        echo "⚠️  UNUSED TABLES FOUND:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($unusedTables as $table) {
            echo "  - $table\n";
            
            // Get row count for each unused table
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['count'];
                echo "    Row count: $count\n";
                
                // Check for foreign key references
                $stmt = $pdo->query("
                    SELECT 
                        TABLE_NAME,
                        COLUMN_NAME,
                        CONSTRAINT_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE REFERENCED_TABLE_NAME = '$table'
                    AND TABLE_SCHEMA = DATABASE()
                ");
                $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($fks)) {
                    echo "    ⚠️  Referenced by foreign keys:\n";
                    foreach ($fks as $fk) {
                        echo "      - {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']}\n";
                    }
                }
            } catch (PDOException $e) {
                echo "    Error: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }
    } else {
        echo "✅ No unused tables found!\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "Analysis complete!\n";
    
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    echo "Please update database credentials in this script or ensure Laravel is properly configured.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

