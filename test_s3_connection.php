#!/usr/bin/env php
<?php
/**
 * S3 Connection Test Script
 * Run this to verify AWS S3 is configured correctly
 * 
 * Usage: php test_s3_connection.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Storage;

// Load Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "===========================================\n";
echo "  AWS S3 Connection Test\n";
echo "===========================================\n\n";

// Check environment variables
echo "1. Checking .env configuration...\n";
$required = ['AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION', 'AWS_BUCKET'];
$missing = [];

foreach ($required as $var) {
    if (empty(env($var))) {
        $missing[] = $var;
        echo "   ❌ $var - NOT SET\n";
    } else {
        $masked = $var === 'AWS_SECRET_ACCESS_KEY' ? '***HIDDEN***' : env($var);
        echo "   ✅ $var - $masked\n";
    }
}

if (!empty($missing)) {
    echo "\n❌ Missing required environment variables!\n";
    echo "Please set these in your .env file:\n";
    foreach ($missing as $var) {
        echo "   - $var\n";
    }
    exit(1);
}

echo "\n2. Testing S3 connection...\n";

try {
    // Test 1: Check if we can connect
    $testFile = 'test_connection_' . time() . '.txt';
    $testContent = 'S3 Connection Test - ' . date('Y-m-d H:i:s');
    
    echo "   Testing file upload...\n";
    Storage::disk('s3')->put($testFile, $testContent);
    echo "   ✅ Upload successful\n";
    
    echo "   Testing file exists...\n";
    $exists = Storage::disk('s3')->exists($testFile);
    echo "   ✅ File exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    echo "   Getting file URL...\n";
    $url = Storage::disk('s3')->url($testFile);
    echo "   ✅ URL: $url\n";
    
    echo "   Reading file content...\n";
    $content = Storage::disk('s3')->get($testFile);
    echo "   ✅ Content matches: " . ($content === $testContent ? 'YES' : 'NO') . "\n";
    
    echo "   Deleting test file...\n";
    Storage::disk('s3')->delete($testFile);
    echo "   ✅ File deleted\n";
    
    echo "\n3. Testing folder structure...\n";
    
    // Test invoice folder
    $invoiceTest = 'invoices/test_' . time() . '.txt';
    Storage::disk('s3')->put($invoiceTest, 'Invoice test');
    echo "   ✅ invoices/ folder works\n";
    echo "   URL: " . Storage::disk('s3')->url($invoiceTest) . "\n";
    Storage::disk('s3')->delete($invoiceTest);
    
    // Test checklist folder
    $checklistTest = 'checklists/test_' . time() . '.txt';
    Storage::disk('s3')->put($checklistTest, 'Checklist test');
    echo "   ✅ checklists/ folder works\n";
    echo "   URL: " . Storage::disk('s3')->url($checklistTest) . "\n";
    Storage::disk('s3')->delete($checklistTest);
    
    echo "\n";
    echo "===========================================\n";
    echo "  ✅ ALL TESTS PASSED!\n";
    echo "  S3 is configured correctly.\n";
    echo "===========================================\n\n";
    
    echo "You can now test the application:\n";
    echo "1. Upload invoice attachment\n";
    echo "2. Upload checklist file\n";
    echo "3. Check AWS S3 Console for files\n\n";
    
    exit(0);
    
} catch (\Exception $e) {
    echo "\n";
    echo "===========================================\n";
    echo "  ❌ TEST FAILED!\n";
    echo "===========================================\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Possible solutions:\n";
    echo "1. Check AWS credentials in .env\n";
    echo "2. Verify IAM user has S3 permissions\n";
    echo "3. Check bucket name is correct\n";
    echo "4. Verify region is correct\n";
    echo "5. Clear config cache: php artisan config:clear\n\n";
    exit(1);
}

