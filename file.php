<?php
$data = json_decode(file_get_contents("php://input"), true);
$baseDir = "uploads/";
$path = isset($data['path']) ? $data['path'] : "";
$fileName = basename($data['name']);
$currentFilePath = realpath($baseDir . $path) . DIRECTORY_SEPARATOR . $fileName;

if (!$currentFilePath || strpos($currentFilePath, realpath($baseDir)) !== 0) {
    echo "Invalid file path.";
    exit;
}

if ($data['action'] === 'delete') {
    if (file_exists($currentFilePath)) {
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
    $newDir = realpath($baseDir . $newPath);
    $newFilePath = $newDir . DIRECTORY_SEPARATOR . $fileName;

    if (!$newDir || strpos($newDir, realpath($baseDir)) !== 0) {
        echo "Invalid target path.";
        exit;
    }

    if (rename($currentFilePath, $newFilePath)) {
        echo "File moved successfully!";
    } else {
        echo "Failed to move file.";
    }
} else {
    echo "Invalid action.";
}
?>
