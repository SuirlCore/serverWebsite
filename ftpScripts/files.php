<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$userLevel = $_SESSION['userLevel'] ?? 0;

$userRoot = "uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);
$secondRoot = "/var/www/html";

// Determine which root to use
if ($userLevel == 1 && isset($_GET['root']) && $_GET['root'] === "admin") {
    $basePath = realpath($secondRoot);
} else {
    $basePath = realpath($userRoot);
}

if (!$basePath) {
    echo json_encode(["error" => "Invalid base directory"]);
    exit();
}

// Get requested path
$currentPath = isset($_GET['path']) ? $_GET['path'] : "";
$fullPath = realpath($basePath . "/" . $currentPath);

// Security check: Prevent path traversal attacks
if (strpos($fullPath, $basePath) !== 0) {
    echo json_encode(["error" => "Access denied"]);
    exit();
}

// Check if the directory exists
if (!is_dir($fullPath)) {
    echo json_encode(["error" => "Directory not found"]);
    exit();
}

// Read directory contents
$filesArray = [];
$dirContents = scandir($fullPath);

foreach ($dirContents as $item) {
    if ($item === "." || $item === "..") {
        continue;
    }
    
    $itemPath = $fullPath . "/" . $item;
    $filesArray[] = [
        "name" => $item,
        "isDir" => is_dir($itemPath),
        "size" => is_file($itemPath) ? filesize($itemPath) : 0
    ];
}

echo json_encode($filesArray);
exit();
?>
