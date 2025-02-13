<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo "0";
    exit();
}

$username = $_SESSION['username'];
$userFolder = "../uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);


function getFolderSize($folder) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

if (is_dir($userFolder)) {
    $size = getFolderSize($userFolder);
    echo round($size / (1024 * 1024 * 1024), 2) . " GB"; // Convert bytes to GB
} else {
    echo "0 GB";
}
?>
