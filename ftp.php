<?php
session_start();

// Mock authentication (Replace this with your actual authentication system)
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'guest';  // Replace with actual username after login
    $_SESSION['userLevel'] = 0;       // Change accordingly
}

$username = $_SESSION['username'];
$userLevel = $_SESSION['userLevel'];

// Define directories
$userDir = "uploads/$username";
$adminDir = "/var/www/html";  // Ensure this is an appropriate location for admin uploads

// Ensure user directory exists
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);  // This ensures the user directory is created with full permissions
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (isset($_FILES['file'])) {
        $targetDir = ($_POST['directory'] === 'admin' && $userLevel == 1) ? $adminDir : $userDir;
        $targetFile = $targetDir . '/' . basename($_FILES['file']['name']);

        // Optional: You can add file validation here (e.g., check file type, size)

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            echo json_encode(["success" => true, "message" => "File uploaded successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to upload file."]);
        }
    } 
    
    // Handle file deletion
    if (isset($_POST['delete'])) {
        $filePath = ($_POST['directory'] === 'admin' && $userLevel == 1) ? $adminDir : $userDir;
        $filePath .= '/' . basename($_POST['delete']);  // Use basename() to avoid directory traversal
    
        // Attempt file deletion
        if (file_exists($filePath) && unlink($filePath)) {
            // Respond with success
            echo json_encode(["success" => true, "message" => "File deleted successfully."]);
        } else {
            // Respond with failure
            echo json_encode(["success" => false, "message" => "Failed to delete file."]);
        }
        exit;  // Ensure the script stops after sending the response
    }
    
}

// Scan files for user and admin
$files = scandir($userDir);
$adminFiles = $userLevel == 1 ? scandir($adminDir) : [];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Server</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 50%; margin: auto; text-align: center; }
        .file-list { margin-top: 20px; }
        .file-list div { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
        <h3>Upload File</h3>
        <input type="file" id="fileInput">
        <button onclick="uploadFile('user')">Upload to User Folder</button>
        <?php if ($userLevel == 1): ?>
            <button onclick="uploadFile('admin')">Upload to Admin Folder</button>
        <?php endif; ?>

        <h3>Your Files</h3>
        <div class="file-list" id="userFiles">
            <?php foreach (array_diff($files, ['.', '..']) as $file): ?>
                <div>
                    <?= htmlspecialchars($file) ?>
                    <button onclick="deleteFile('<?= htmlspecialchars($file) ?>', 'user')">Delete</button>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($userLevel == 1): ?>
            <h3>Admin Files</h3>
            <div class="file-list" id="adminFiles">
                <?php foreach (array_diff($adminFiles, ['.', '..']) as $file): ?>
                    <div>
                        <?= htmlspecialchars($file) ?>
                        <button onclick="deleteFile('<?= htmlspecialchars($file) ?>', 'admin')">Delete</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Function for uploading file
        function uploadFile(directory) {
            let fileInput = document.getElementById("fileInput");
            if (!fileInput.files.length) return alert("Select a file first!");
            
            let formData = new FormData();
            formData.append("file", fileInput.files[0]);
            formData.append("directory", directory);
            
            fetch("", { method: "POST", body: formData })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                });
        }

        // Function for deleting file
        function deleteFile(fileName, directory) {
            if (!confirm("Are you sure you want to delete " + fileName + "?")) return;
            
            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ delete: fileName, directory: directory })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                location.reload();
            });
        }
    </script>
</body>
</html>
