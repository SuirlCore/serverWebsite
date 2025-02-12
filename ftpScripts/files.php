<?php
$baseDir = "../uploads/";
$path = isset($_GET['path']) ? $_GET['path'] : "";
$directory = realpath($baseDir . $path);

if (!$directory || strpos($directory, realpath($baseDir)) !== 0) {
    echo json_encode([]);
    exit;
}

$files = array_diff(scandir($directory), ['.', '..']);
$fileList = [];

foreach ($files as $file) {
    $fileList[] = [
        'name' => $file,
        'isDir' => is_dir($directory . DIRECTORY_SEPARATOR . $file)
    ];
}

echo json_encode($fileList);
?>
