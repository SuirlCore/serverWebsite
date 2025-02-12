<?php
$data = json_decode(file_get_contents("php://input"), true);
$baseDir = ".//uploads/";
$path = isset($data['path']) ? $data['path'] : "";
$folderName = basename($data['name']);
$folderPath = realpath($baseDir . $path) . DIRECTORY_SEPARATOR . $folderName;

if (!$folderPath || strpos($folderPath, realpath($baseDir)) !== 0) {
    echo "Invalid folder path.";
    exit;
}

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
        if (count(scandir($folderPath)) == 2) { // Check if empty
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
