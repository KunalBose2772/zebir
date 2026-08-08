<?php
/**
 * Image WebP Converter Tool - Production / Local
 * ZEBIR LIBAS
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Enable error reporting for this script to see if anything fails
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent timeout
set_time_limit(600);

echo "<h1>Starting Image WebP Conversion...</h1>";
echo "<pre>";

$dirs = [
    'uploads/banners/' => BASE_PATH . 'uploads/banners/',
    'uploads/products/' => BASE_PATH . 'uploads/products/',
    'assets/images/' => BASE_PATH . 'assets/images/',
];

$totalConverted = 0;
$totalBytesSaved = 0;

foreach ($dirs as $label => $path) {
    if (!is_dir($path)) {
        echo "Directory not found: $label ($path)\n";
        continue;
    }

    echo "\nScanning $label...\n";
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $path . $file;
        if (is_dir($filePath)) {
            continue;
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $webpFile = pathinfo($file, PATHINFO_FILENAME) . '.webp';
            $webpPath = $path . $webpFile;

            if (file_exists($webpPath)) {
                echo "  [Skipped] $file - WebP already exists.\n";
                continue;
            }

            $sizeBefore = filesize($filePath);
            $success = resizeAndConvertToWebP($filePath, $webpPath, 1920, 1920, 82);

            if ($success) {
                $sizeAfter = filesize($webpPath);
                $savings = $sizeBefore - $sizeAfter;
                $totalConverted++;
                $totalBytesSaved += $savings;
                echo "  [Converted] $file -> $webpFile (" . round($sizeBefore/1024, 1) . " KB -> " . round($sizeAfter/1024, 1) . " KB, saved " . round($savings/1024, 1) . " KB)\n";
            } else {
                echo "  [Failed] $file - Conversion failed.\n";
            }
        }
    }
}

echo "\nConversion Complete!\n";
echo "Total Converted: $totalConverted\n";
echo "Total Saved: " . round($totalBytesSaved / (1024 * 1024), 2) . " MB\n";
echo "</pre>";
