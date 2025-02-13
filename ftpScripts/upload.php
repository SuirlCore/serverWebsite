<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo "Unauthorized access!";
    exit();
}

$username = $_SESSION['username'];
$userLevel = $_SESSION['userLevel'];

$userRoot = "/var/www/html/serverWebpage/uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);
$adminRoot = ($userLevel == 1) ? "/var/www/html" : null;

$selectedRoot = isset($_POST['root']) && $userLevel == 1 && $_POST['root'] === "admin" ? $adminRoot : $userRoot;
$currentPath = isset($_POST['path']) ? $_POST['path'] : "";

// Combine paths and resolve the real path
$targetDir = realpath($selectedRoot . "/" . ltrim($currentPath, "/"));

if (!$targetDir || !is_dir($targetDir)) {
    echo "Invalid directory!";
    exit();
}

if (isset($_FILES['file'])) {
    $files = $_FILES['file'];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = basename($files['name'][$i]);
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];

        $targetFilePath = $targetDir . '/' . $fileName;

        if (file_exists($targetFilePath)) {
            echo "File $fileName already exists!<br>";
            continue;
        }

        if (move_uploaded_file($fileTmpName, $targetFilePath)) {
            echo "File $fileName uploaded successfully.<br>";
        } else {
            echo "Error uploading $fileName.<br>";
        }
    }
} else {
    echo "No files were uploaded.<br>";
}
?>
