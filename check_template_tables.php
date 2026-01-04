<?php
/**
 * Script to check if templates and template_infos tables are empty
 * Run this from command line: php check_template_tables.php
 * 
 * Make sure to update database credentials below before running
 */

// Database connection - UPDATE THESE WITH YOUR CREDENTIALS
$host = '127.0.0.1';
$dbname = 'your_database_name'; // UPDATE THIS
$username = 'root'; // UPDATE THIS
$password = ''; // UPDATE THIS

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Template Tables Check ===\n\n";
    
    // Check if tables exist
    $tables = ['templates', 'template_infos'];
    
    foreach ($tables as $table) {
        echo "Checking table: $table\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0;
            
            if (!$exists) {
                echo "❌ Table '$table' does NOT exist in database\n\n";
                continue;
            }
            
            echo "✓ Table '$table' exists\n";
            
            // Get row count
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'];
            
            if ($count == 0) {
                echo "✓ Table is EMPTY (0 rows)\n";
                echo "✅ SAFE TO REMOVE\n";
            } else {
                echo "⚠️  Table has $count row(s)\n";
                echo "❌ NOT SAFE TO REMOVE - Contains data\n";
                
                // Show sample data
                $stmt = $pdo->query("SELECT * FROM $table LIMIT 5");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "\nSample data (first 5 rows):\n";
                print_r($rows);
            }
            
            // Get table structure
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "\nTable structure:\n";
            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
            
        } catch (PDOException $e) {
            echo "❌ Error checking table: " . $e->getMessage() . "\n";
        }
        
        echo "\n" . str_repeat("=", 50) . "\n\n";
    }
    
    // Check for foreign key relationships
    echo "Checking for foreign key references:\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $stmt = $pdo->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME IN ('templates', 'template_infos')
            AND TABLE_SCHEMA = DATABASE()
        ");
        
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($fks)) {
            echo "✓ No foreign keys referencing these tables\n";
        } else {
            echo "⚠️  Found foreign key references:\n";
            foreach ($fks as $fk) {
                echo "  - {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} references {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
            }
        }
    } catch (PDOException $e) {
        echo "❌ Error checking foreign keys: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Check complete!\n";
    
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    echo "Please update database credentials in this script.\n";
}

