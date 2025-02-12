<?php
$uploadDir = '../uploads'; // Adjust this path if needed
$totalSpace = 10 * 1024 * 1024 * 1024; // 10GB in bytes

function getFolderSize($folder) {
    $size = 0;
    foreach (glob(rtrim($folder, '/') . '/*', GLOB_NOSORT) as $file) {
        $size += is_file($file) ? filesize($file) : getFolderSize($file);
    }
    return $size;
}

$usedSpace = getFolderSize($uploadDir);
echo round($usedSpace / (1024 * 1024 * 1024), 2) . 'GB';
?>
