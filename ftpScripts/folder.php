<?php
session_start();

if (!isset($_SESSION['username'])) {
    die("Unauthorized.");
}

$username = $_SESSION['username'];
$userFolder = "../uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);

// Function to calculate the folder size
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

// Get the folder name from the request
$data = json_decode(file_get_contents("php://input"), true);
$folderName = preg_replace("/[^a-zA-Z0-9_-]/", "_", $data['name']);
$folderPath = $userFolder . "/" . $folderName;

// Check if the folder already exists
if (!file_exists($folderPath)) {
    // Create the folder with 0777 permissions for full access to all users
    if (mkdir($folderPath, 0777, true)) {
        // Set the folder permissions to 0777 (read/write/execute for everyone)
        chmod($folderPath, 0777);

        echo "Folder created successfully.";
    } else {
        echo "Failed to create folder.";
    }
} else {
    echo "Folder already exists.";
}
?>
