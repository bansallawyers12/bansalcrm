<?php
/**
 * Font Cleanup Script
 * Identifies and optionally removes unused font files from public/fonts directory
 * 
 * Usage: php cleanup_unused_fonts.php [--delete]
 * 
 * Without --delete flag: Only lists unused fonts
 * With --delete flag: Lists and removes unused fonts (with confirmation)
 */

$basePath = __DIR__;
$fontsDir = $basePath . '/public/fonts';

// Fonts that are known to be used
$usedFonts = [
    // monofont.ttf - used in Controller.php for CAPTCHA
    'monofont.ttf',
    
    // fontawesome-webfont - referenced in font-awesome.min.css
    'fontawesome-webfont.eot',
    'fontawesome-webfont.woff2',
    'fontawesome-webfont.woff',
    'fontawesome-webfont.ttf',
    'fontawesome-webfont.svg',
    
    // summernote fonts - referenced in summernote CSS files
    'summernote.eot',
    'summernote.woff',
    'summernote.ttf',
    'summernote4c4d.eot',
    'summernote4c4d.woff',
    'summernote4c4d.ttf',
    'summernoted41d.eot',
];

// Get all font files recursively
function getAllFontFiles($dir) {
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
                'relative' => $relativePath,
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
            ];
        }
    }
    
    return $files;
}

// Check if font is referenced in codebase
function isFontReferenced($fontName, $basePath) {
    $fontBaseName = pathinfo($fontName, PATHINFO_FILENAME);
    $fontExtension = pathinfo($fontName, PATHINFO_EXTENSION);
    
    // Search patterns
    $patterns = [
        // Direct filename references
        '/' . preg_quote($fontName, '/') . '/i',
        // Base name references (for different extensions)
        '/' . preg_quote($fontBaseName, '/') . '[^a-zA-Z0-9]/i',
        // URL references
        '/fonts\/' . preg_quote($fontName, '/') . '/i',
        '/fonts\/' . preg_quote($fontBaseName, '/') . '[^a-zA-Z0-9]/i',
    ];
    
    // Directories to search
    $searchDirs = [
        $basePath . '/public/css',
        $basePath . '/public/js',
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
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'css', 'js', 'blade.php', 'scss', 'sass'])) {
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

// Main execution
echo "=== Font Cleanup Script ===\n\n";
echo "Scanning fonts directory: {$fontsDir}\n";

if (!is_dir($fontsDir)) {
    echo "ERROR: Fonts directory not found: {$fontsDir}\n";
    exit(1);
}

$allFonts = getAllFontFiles($fontsDir);
echo "Found " . count($allFonts) . " font files\n\n";

$unusedFonts = [];
$usedFontsFound = [];
$totalSize = 0;

foreach ($allFonts as $font) {
    $fontName = $font['name'];
    $fontRelative = $font['relative'];
    
    // Check if it's in our known used fonts list
    $isKnownUsed = false;
    foreach ($usedFonts as $usedFont) {
        if (stripos($fontName, $usedFont) !== false || stripos($fontRelative, $usedFont) !== false) {
            $isKnownUsed = true;
            $usedFontsFound[] = $fontRelative;
            break;
        }
    }
    
    // If not in known list, check if referenced in codebase
    if (!$isKnownUsed) {
        if (!isFontReferenced($fontName, $basePath) && !isFontReferenced($fontRelative, $basePath)) {
            $unusedFonts[] = $font;
            $totalSize += $font['size'];
        } else {
            $usedFontsFound[] = $fontRelative;
        }
    }
}

// Display results
echo "=== RESULTS ===\n\n";
echo "Used fonts found: " . count($usedFontsFound) . "\n";
echo "Unused fonts found: " . count($unusedFonts) . "\n";
echo "Total size of unused fonts: " . formatBytes($totalSize) . "\n\n";

if (count($unusedFonts) > 0) {
    echo "=== UNUSED FONTS ===\n";
    foreach ($unusedFonts as $font) {
        echo "  - {$font['relative']} (" . formatBytes($font['size']) . ")\n";
    }
    echo "\n";
    
    // Check if --delete flag is provided
    $delete = in_array('--delete', $argv);
    
    if ($delete) {
        echo "=== DELETION ===\n";
        echo "WARNING: You are about to delete " . count($unusedFonts) . " unused font files.\n";
        echo "Total size to be freed: " . formatBytes($totalSize) . "\n\n";
        echo "Type 'yes' to confirm deletion: ";
        
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($line) === 'yes') {
            $deleted = 0;
            $errors = 0;
            
            foreach ($unusedFonts as $font) {
                if (@unlink($font['path'])) {
                    $deleted++;
                    echo "  ✓ Deleted: {$font['relative']}\n";
                } else {
                    $errors++;
                    echo "  ✗ Failed to delete: {$font['relative']}\n";
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
        echo "To delete these files, run: php cleanup_unused_fonts.php --delete\n";
    }
} else {
    echo "No unused fonts found. All fonts appear to be in use.\n";
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

