<?php
$baseDir = "uploads/";
$maxStorage = 10 * 1024 * 1024 * 1024; // 10GB in bytes
$path = isset($_POST['path']) ? $_POST['path'] : "";
$uploadDir = realpath($baseDir . $path);

if (!$uploadDir || strpos($uploadDir, realpath($baseDir)) !== 0) {
    echo "Invalid upload path.";
    exit;
}

function getDirectorySize($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

$currentSize = getDirectorySize($baseDir);
$fileSize = $_FILES['file']['size'];

if ($currentSize + $fileSize > $maxStorage) {
    echo "Upload failed! Storage limit exceeded (10GB max).";
    exit;
}

if (!empty($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $targetFile = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo "File uploaded successfully!";
    } else {
        echo "File upload failed.";
    }
}
?>
