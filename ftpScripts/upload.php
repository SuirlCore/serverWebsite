<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Unauthorized.");
}

$username = $_SESSION['username'];
$userFolder = "../uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);


if (!is_dir($userFolder)) {
    mkdir($userFolder, 0777, true);
}

function getFolderSize($folder) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

$maxSize = 10 * 1024 * 1024 * 1024; // 10GB in bytes
$currentSize = getFolderSize($userFolder);

if ($_FILES['file']['size'] + $currentSize > $maxSize) {
    die("Upload failed: Storage limit exceeded (10GB).");
}

$targetFile = $userFolder . "/" . basename($_FILES["file"]["name"]);

if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
    echo "Upload successful.";
} else {
    echo "Upload failed.";
}
?>
