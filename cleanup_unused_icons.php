<?php
/**
 * Icons Cleanup Script
 * Identifies unused icon files and broken references in the icons directory
 * 
 * Usage: php cleanup_unused_icons.php [--delete]
 * 
 * Without --delete flag: Only lists unused icons and broken references
 * With --delete flag: Lists and removes unused icons (with confirmation)
 */

$basePath = __DIR__;
$iconsDir = $basePath . '/public/icons';

// Known used icon files/folders
$usedIcons = [
    // Font Awesome - used in adminnew.blade.php
    'font-awesome/css/all.min.css',
    'font-awesome/fonts/fa-brands-400.eot',
    'font-awesome/fonts/fa-brands-400.woff2',
    'font-awesome/fonts/fa-brands-400.woff',
    'font-awesome/fonts/fa-brands-400.ttf',
    'font-awesome/fonts/fa-brands-400.svg',
    'font-awesome/fonts/fa-regular-400.eot',
    'font-awesome/fonts/fa-regular-400.woff2',
    'font-awesome/fonts/fa-regular-400.woff',
    'font-awesome/fonts/fa-regular-400.ttf',
    'font-awesome/fonts/fa-regular-400.svg',
    'font-awesome/fonts/fa-solid-900.eot',
    'font-awesome/fonts/fa-solid-900.woff2',
    'font-awesome/fonts/fa-solid-900.woff',
    'font-awesome/fonts/fa-solid-900.ttf',
    'font-awesome/fonts/fa-solid-900.svg',
];

// Get all icon files recursively
function getAllIconFiles($dir) {
    $files = [];
    if (!is_dir($dir)) {
        return $files;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $files[] = [
                'path' => $file->getPathname(),
                'relative' => str_replace('\\', '/', $relativePath),
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
            ];
        }
    }
    
    return $files;
}

// Check if icon is referenced in codebase
function isIconReferenced($iconPath, $basePath) {
    $iconName = basename($iconPath);
    $iconDir = dirname($iconPath);
    
    // Search patterns
    $patterns = [
        // Direct path references
        '/' . preg_quote($iconPath, '/') . '/i',
        // Relative path references
        '/icons\/' . preg_quote($iconPath, '/') . '/i',
        // Just filename
        '/' . preg_quote($iconName, '/') . '/i',
    ];
    
    // Directories to search
    $searchDirs = [
        $basePath . '/public',
        $basePath . '/app',
        $basePath . '/resources',
    ];
    
    foreach ($searchDirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'css', 'js', 'blade.php', 'scss', 'sass', 'html'])) {
                $content = @file_get_contents($file->getPathname());
                if ($content === false) {
                    continue;
                }
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        return true;
                    }
                }
            }
        }
    }
    
    return false;
}

// Check for broken references (files referenced but don't exist)
function findBrokenReferences($basePath) {
    $brokenRefs = [];
    
    $searchDirs = [
        $basePath . '/app',
        $basePath . '/resources',
    ];
    
    $iconPattern = '/icons\/([^\s"\'\)]+)/i';
    
    foreach ($searchDirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                $content = @file_get_contents($file->getPathname());
                if ($content === false) {
                    continue;
                }
                
                if (preg_match_all($iconPattern, $content, $matches)) {
                    foreach ($matches[1] as $iconRef) {
                        $iconPath = $basePath . '/public/icons/' . $iconRef;
                        if (!file_exists($iconPath)) {
                            $brokenRefs[] = [
                                'file' => str_replace($basePath . '/', '', $file->getPathname()),
                                'reference' => 'icons/' . $iconRef,
                            ];
                        }
                    }
                }
            }
        }
    }
    
    return $brokenRefs;
}

// Main execution
echo "=== Icons Cleanup Script ===\n\n";
echo "Scanning icons directory: {$iconsDir}\n";

if (!is_dir($iconsDir)) {
    echo "ERROR: Icons directory not found: {$iconsDir}\n";
    exit(1);
}

$allIcons = getAllIconFiles($iconsDir);
echo "Found " . count($allIcons) . " icon files\n\n";

$unusedIcons = [];
$usedIconsFound = [];
$totalSize = 0;

foreach ($allIcons as $icon) {
    $iconRelative = $icon['relative'];
    
    // Check if it's in our known used icons list
    $isKnownUsed = false;
    foreach ($usedIcons as $usedIcon) {
        if (stripos($iconRelative, $usedIcon) !== false) {
            $isKnownUsed = true;
            $usedIconsFound[] = $iconRelative;
            break;
        }
    }
    
    // If not in known list, check if referenced in codebase
    if (!$isKnownUsed) {
        if (!isIconReferenced($iconRelative, $basePath)) {
            $unusedIcons[] = $icon;
            $totalSize += $icon['size'];
        } else {
            $usedIconsFound[] = $iconRelative;
        }
    }
}

// Find broken references
$brokenRefs = findBrokenReferences($basePath);

// Display results
echo "=== RESULTS ===\n\n";
echo "Used icons found: " . count($usedIconsFound) . "\n";
echo "Unused icons found: " . count($unusedIcons) . "\n";
echo "Total size of unused icons: " . formatBytes($totalSize) . "\n";
echo "Broken references found: " . count($brokenRefs) . "\n\n";

if (count($brokenRefs) > 0) {
    echo "=== BROKEN REFERENCES (files referenced but don't exist) ===\n";
    foreach ($brokenRefs as $ref) {
        echo "  - {$ref['file']} references: {$ref['reference']}\n";
    }
    echo "\n";
}

if (count($unusedIcons) > 0) {
    echo "=== UNUSED ICONS ===\n";
    foreach ($unusedIcons as $icon) {
        echo "  - {$icon['relative']} (" . formatBytes($icon['size']) . ")\n";
    }
    echo "\n";
    
    // Check if --delete flag is provided
    $delete = in_array('--delete', $argv);
    
    if ($delete) {
        echo "=== DELETION ===\n";
        echo "WARNING: You are about to delete " . count($unusedIcons) . " unused icon files.\n";
        echo "Total size to be freed: " . formatBytes($totalSize) . "\n\n";
        echo "Type 'yes' to confirm deletion: ";
        
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) === 'yes') {
            $deleted = 0;
            $errors = 0;
            
            foreach ($unusedIcons as $icon) {
                if (@unlink($icon['path'])) {
                    $deleted++;
                    echo "  ✓ Deleted: {$icon['relative']}\n";
                } else {
                    $errors++;
                    echo "  ✗ Failed to delete: {$icon['relative']}\n";
                }
            }
            
            echo "\n=== SUMMARY ===\n";
            echo "Deleted: {$deleted} files\n";
            echo "Errors: {$errors} files\n";
            echo "Space freed: " . formatBytes($totalSize) . "\n";
        } else {
            echo "Deletion cancelled.\n";
        }
    } else {
        echo "To delete these files, run: php cleanup_unused_icons.php --delete\n";
    }
} else {
    echo "No unused icons found. All icons appear to be in use.\n";
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

