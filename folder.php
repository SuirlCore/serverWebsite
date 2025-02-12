<?php
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['action']) || !isset($data['name'])) {
    echo "Invalid request";
    exit;
}

$baseDir = "uploads/";
$folderName = basename($data['name']); // Prevent directory traversal
$folderPath = $baseDir . $folderName;

if ($data['action'] === 'create') {
    if (!file_exists($folderPath)) {
        if (mkdir($folderPath, 0777, true)) {
            echo "Folder created successfully!";
        } else {
            echo "Failed to create folder.";
        }
    } else {
        echo "Folder already exists.";
    }
} elseif ($data['action'] === 'delete') {
    if (is_dir($folderPath)) {
        if (count(scandir($folderPath)) == 2) { // Ensure folder is empty
            if (rmdir($folderPath)) {
                echo "Folder deleted successfully!";
            } else {
                echo "Failed to delete folder.";
            }
        } else {
            echo "Folder is not empty!";
        }
    } else {
        echo "Folder does not exist.";
    }
} else {
    echo "Invalid action.";
}
?>
