<?php
session_start();

// Ensure the user is authenticated
if (!isset($_SESSION['username'])) {
    die(json_encode(["error" => "Unauthorized"]));
}

$username = $_SESSION['username'];
$userLevel = $_SESSION['userLevel'] ?? 0;

// Get requested root directory (if allowed)
$requested_root = $_POST['requested_root'] ?? 'user';
$root_dir = ($userLevel == 1 && $requested_root == 'server') ? '/var/www/html' : __DIR__ . "/../uploads/$username";
$user_dir = realpath($root_dir); // Prevent directory traversal

if (!$user_dir || !is_dir($user_dir)) {
    die(json_encode(["error" => "Invalid home directory"]));
}

$action = $_POST['action'] ?? '';
$current_dir = $_POST['current_dir'] ?? '';

// Prevent directory traversal by resolving real paths
$current_path = realpath($user_dir . '/' . $current_dir);
if (!$current_path || strpos($current_path, $user_dir) !== 0) {
    die(json_encode(["error" => "Access denied"]));
}

switch ($action) {
    case 'upload':
        if (!empty($_FILES['file']['name'])) {
            $target = $current_path . '/' . basename($_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $target);
        }
        break;

    case 'delete':
        $file = $_POST['file'] ?? '';
        if ($file && strpos($file, '..') === false) {
            unlink($current_path . '/' . $file);
        }
        break;

    case 'create_folder':
        $folder = $_POST['folder'] ?? '';
        if ($folder && strpos($folder, '..') === false) {
            mkdir($current_path . '/' . $folder, 0777, true);
        }
        break;

    case 'delete_folder':
        $folder = $_POST['folder'] ?? '';
        $folder_path = $current_path . '/' . $folder;
        if ($folder && is_dir($folder_path)) {
            $files = array_diff(scandir($folder_path), ['.', '..']);
            if (empty($files)) {
                rmdir($folder_path);
            } else {
                die(json_encode(["error" => "Folder is not empty"]));
            }
        }
        break;

    case 'move':
        $source = $_POST['source'] ?? '';
        $destination = $_POST['destination'] ?? '';
        if ($source && $destination && strpos($source, '..') === false && strpos($destination, '..') === false) {
            $source_path = realpath($current_path . '/' . $source);
            $destination_path = $current_path . '/' . $destination . '/' . basename($source);
            if ($source_path && strpos($source_path, $user_dir) === 0) {
                rename($source_path, $destination_path);
            }
        }
        break;

    case 'move_up':
        $source = $_POST['source'] ?? '';
        $parent_dir = dirname($current_path);
    
        if ($source && strpos($source, '..') === false) {
            $source_path = realpath($current_path . '/' . $source);
            $destination_path = $parent_dir . '/' . $source;
    
            // Check if parent_dir is still within the allowed root
            if ($source_path && strpos($source_path, $user_dir) === 0 && strpos($destination_path, $user_dir) === 0) {
                if ($parent_dir !== $user_dir) {  // Allow moving up but stop at user_dir
                    rename($source_path, $destination_path);
                } else {
                    die(json_encode(["error" => "Cannot move up any further"]));
                }
            }
        }
        break;
}

// Fetch files and folders in the current directory
$files = array_diff(scandir($current_path), ['.', '..']);

$items = [];
foreach ($files as $file) {
    $items[] = [
        'name' => $file,
        'type' => is_dir($current_path . '/' . $file) ? 'folder' : 'file'
    ];
}

header('Content-Type: application/json');
echo json_encode(["files" => $items, "current_root" => $root_dir]);
exit;
?>
