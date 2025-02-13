<?php
session_start();  // Make sure the session is started to access the username

// Retrieve user information
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
if (!$username) {
    echo "Unauthorized access!";
    exit;
}

// Get the data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Base directory for uploads - ensure this points to the correct absolute path
$baseDir = realpath("../uploads/") . DIRECTORY_SEPARATOR . $username;  // User's specific folder

if (!$baseDir) {
    echo "Base directory does not exist.";
    exit;
}

$path = isset($data['path']) ? $data['path'] : "";
$fileName = basename($data['name']);  // Sanitize the file name
$currentFilePath = realpath($baseDir . DIRECTORY_SEPARATOR . $path) . DIRECTORY_SEPARATOR . $fileName;

// Validate that the current file path is within the user's folder
if (!$currentFilePath || strpos($currentFilePath, $baseDir) !== 0) {
    echo "Invalid file path.";
    exit;
}

// Handle delete action
if ($data['action'] === 'delete') {
    if (file_exists($currentFilePath)) {
        $fileSize = filesize($currentFilePath);
        if (unlink($currentFilePath)) {
            echo "File deleted successfully!";
        } else {
            echo "Failed to delete file.";
        }
    } else {
        echo "File does not exist.";
    }
} elseif ($data['action'] === 'move') {
    $newPath = isset($data['newPath']) ? $data['newPath'] : "";
    $newDir = realpath($baseDir . DIRECTORY_SEPARATOR . $newPath);

    if (!$newDir || strpos($newDir, $baseDir) !== 0) {
        echo "Invalid target path.";
        exit;
    }

    $newFilePath = $newDir . DIRECTORY_SEPARATOR . $fileName;

    if (rename($currentFilePath, $newFilePath)) {
        echo "File moved successfully!";
    } else {
        echo "Failed to move file.";
    }
} else {
    echo "Invalid action.";
}
?>
