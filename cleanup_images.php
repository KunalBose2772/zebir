<?php
/**
 * Image Cleanup Script - Removes legacy PNG/JPG/JPEG files if their WebP versions exist
 * ZEBIR LIBAS
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Starting Image Cleanup...</h1>";
echo "<pre>";

$dirs = [
    'uploads/banners/' => BASE_PATH . 'uploads/banners/',
    'uploads/products/' => BASE_PATH . 'uploads/products/',
    'assets/images/' => BASE_PATH . 'assets/images/',
];

$totalDeleted = 0;
$totalBytesFreed = 0;

foreach ($dirs as $label => $path) {
    if (!is_dir($path)) {
        echo "Directory not found: $label ($path)\n";
        continue;
    }

    echo "\nScanning $label for redundant originals...\n";
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

            // If the WebP version exists, the original is redundant
            if (file_exists($webpPath)) {
                $size = @filesize($filePath);
                $deleted = @unlink($filePath);
                if ($deleted) {
                    $totalDeleted++;
                    $totalBytesFreed += $size;
                    echo "  [Deleted] $file (" . round($size/1024, 1) . " KB freed)\n";
                } else {
                    echo "  [Failed to delete] $file\n";
                }
            }
        }
    }
}

echo "\nImage Cleanup Complete!\n";
echo "Total Redundant Files Deleted: $totalDeleted\n";
echo "Total Space Freed: " . round($totalBytesFreed / (1024 * 1024), 2) . " MB\n";
echo "</pre>";
