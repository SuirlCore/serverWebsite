<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Unauthorized.");
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

$maxSize = 10 * 1024 * 1024 * 1024; // 10GB limit
$currentSize = getFolderSize($userFolder);

if ($currentSize >= $maxSize) {
    die("Cannot create folder: Storage limit exceeded (10GB).");
}

$data = json_decode(file_get_contents("php://input"), true);
$folderName = preg_replace("/[^a-zA-Z0-9_-]/", "_", $data['name']);
$folderPath = $userFolder . "/" . $folderName;

if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
    echo "Folder created.";
} else {
    echo "Folder already exists.";
}
?>
