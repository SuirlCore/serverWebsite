<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$userLevel = $_SESSION['userLevel'] ?? 0;

// Define the user's root directory (uploads)
$userRoot = "../uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);
// Define the admin root directory (server root for admins)
$secondRoot = "/var/www/html";

// Determine which root to use based on user level and the 'root' query parameter
if ($userLevel == 1 && isset($_GET['root']) && $_GET['root'] === "admin") {
    // Admin path if root is specified as "admin"
    $basePath = realpath($secondRoot);
} else {
    // User path if no root or regular user
    $basePath = realpath($userRoot);
}

if (!$basePath) {
    echo json_encode(["error" => "Invalid base directory"]);
    exit();
}

// Get the requested path from the query parameter
$currentPath = isset($_GET['path']) ? $_GET['path'] : "";

// Construct the full path by combining base path and requested path
$fullPath = realpath($basePath . "/" . $currentPath);

// Security check: Prevent path traversal attacks by ensuring full path is within basePath
if (strpos($fullPath, $basePath) !== 0) {
    echo json_encode(["error" => "Access denied"]);
    exit();
}

// Check if the directory exists
if (!is_dir($fullPath)) {
    echo json_encode(["error" => "Directory not found"]);
    exit();
}

// Read the directory contents
$filesArray = [];
$dirContents = scandir($fullPath);

// Iterate over the directory contents
foreach ($dirContents as $item) {
    if ($item === "." || $item === "..") {
        continue; // Skip current and parent directory references
    }

    // Get the full path of each item (file or folder)
    $itemPath = $fullPath . "/" . $item;

    // Add the item to the response array
    $filesArray[] = [
        "name" => $item,
        "isDir" => is_dir($itemPath),
        "size" => is_file($itemPath) ? filesize($itemPath) : 0
    ];
}

// Return the list of files (or an empty array if no files are found)
echo json_encode($filesArray);
exit();
?>
