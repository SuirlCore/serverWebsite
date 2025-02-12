<?php
$baseDir = "uploads/";
$path = isset($_POST['path']) ? $_POST['path'] : "";
$uploadDir = realpath($baseDir . $path);

if (!$uploadDir || strpos($uploadDir, realpath($baseDir)) !== 0) {
    echo "Invalid upload path.";
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
