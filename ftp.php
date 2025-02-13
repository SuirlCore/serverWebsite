<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username']; // Get the logged-in username
$userLevel = $_SESSION['userLevel']; // Get user level

// Define primary user root
$userRoot = "uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username);

// Define secondary root for admins (userLevel == 1)
$adminRoot = ($userLevel == 1) ? "/var/www/html" : null;

// Determine the selected root directory
$selectedRoot = isset($_GET['root']) && $userLevel == 1 && $_GET['root'] === "admin" ? $adminRoot : $userRoot;

// Prevent directory traversal attacks
$currentPath = isset($_GET['path']) ? $_GET['path'] : "";
$realBase = realpath($selectedRoot);
$realPath = realpath($selectedRoot . "/" . $currentPath);

if ($realPath === false || strpos($realPath, $realBase) !== 0) {
    $currentPath = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Server</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: auto; }
        .file-list { margin-top: 20px; }
        .file-item { display: flex; justify-content: space-between; padding: 5px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="container">
        <h2>File Server</h2>

        <p>Storage Used: <span id="storageUsed">Calculating...</span> 
            <span id="storageLimit"></span>
        </p>

        <?php if ($userLevel == 1): ?>
            <button onclick="switchRoot('user')">User Folder</button>
            <button onclick="switchRoot('admin')">Admin Root</button>
        <?php endif; ?>

        <input type="file" id="fileInput">
        <button onclick="uploadFile()">Upload</button>
        
        <input type="text" id="folderName" placeholder="Folder Name">
        <button onclick="createFolder()">Create Folder</button>
        
        <input type="text" id="currentPath" value="<?php echo htmlspecialchars($currentPath); ?>" readonly>
        <button id="goUpButton" onclick="navigateUp()">Go Up</button>
        
        <div class="file-list" id="fileList"></div>
    </div>

    <script>
        let userRoot = "<?php echo $userRoot; ?>";
        let adminRoot = "<?php echo $adminRoot ? $adminRoot : ''; ?>";
        let userLevel = <?php echo $userLevel; ?>;
        let selectedRoot = "<?php echo $selectedRoot; ?>";
        let currentPath = "<?php echo $currentPath; ?>";

        function switchRoot(root) {
            let rootPath = (root === 'admin') ? adminRoot : userRoot;
            window.location.href = `?root=${root}&path=`;
        }

        function fetchFiles() {
            fetch(`ftpScripts/files.php?path=${encodeURIComponent(currentPath)}`)
            .then(response => response.json())
            .then(files => {
                if (!Array.isArray(files)) {
                    console.error("Error: Response is not an array", files);
                    return;
                }
                let fileList = document.getElementById('fileList');
                fileList.innerHTML = '';
                files.forEach(file => {
                    let div = document.createElement('div');
                    div.className = 'file-item';
                    if (file.isDir) {
                        div.innerHTML = `<span onclick="navigateTo('${file.name}')" style="cursor: pointer; color: blue;">📁 ${file.name}</span> <button onclick="deleteFolder('${file.name}')">Delete</button>`;
                    } else {
                        div.innerHTML = `<span>${file.name}</span> <a href='${userRoot}/${currentPath}/${file.name}' download>Download</a> <button onclick="deleteFile('${file.name}')">Delete</button> <button onclick="moveFile('${file.name}')">Move</button>`;
                    }
                    fileList.appendChild(div);
                });
            })
            .catch(error => console.error("Fetch error:", error));
        }

        function updateGoUpButton() {
            document.getElementById("goUpButton").disabled = (currentPath === "");
        }

        function fetchStorageUsed() {
            if (selectedRoot === adminRoot) {
                document.getElementById('storageUsed').innerText = "N/A";
                document.getElementById('storageLimit').innerText = "(No Limit)";
            } else {
                fetch('ftpScripts/storage.php')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('storageUsed').innerText = data;
                        document.getElementById('storageLimit').innerText = "/ 10GB";
                        let used = parseFloat(data);
                        if (used >= 10) {
                            alert("Warning: You have reached your 10GB storage limit!");
                        }
                    });
            }
        }

        function uploadFile() {
            let fileInput = document.getElementById('fileInput');
            let file = fileInput.files[0];
            let formData = new FormData();
            formData.append('file', file);
            formData.append('path', currentPath);
            formData.append('root', selectedRoot);

            fetch('ftpScripts/upload.php', {
                method: 'POST',
                body: formData
            }).then(response => response.text())
              .then(data => {
                  alert(data);
                  fetchFiles();
              });
        }

        function createFolder() {
            let folderName = document.getElementById('folderName').value;
            fetch('ftpScripts/folder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'create', name: folderName, path: currentPath, root: selectedRoot })
            }).then(response => response.text())
              .then(data => {
                  alert(data);
                  fetchFiles();
              });
        }

        function deleteFolder(folderName) {
            fetch('ftpScripts/folder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', name: folderName, path: currentPath, root: selectedRoot })
            }).then(response => response.text())
              .then(data => {
                  alert(data);
                  fetchFiles();
              });
        }

        function deleteFile(fileName) {
            fetch('ftpScripts/file.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', name: fileName, path: currentPath, root: selectedRoot })
            }).then(response => response.text())
              .then(data => {
                  alert(data);
                  fetchFiles();
              });
        }

        function navigateTo(folderName) {
            currentPath = currentPath ? `${currentPath}/${folderName}` : folderName;
            fetchFiles();
        }

        function navigateUp() {
            if (currentPath === "") return; // Prevent navigating above root
            let pathParts = currentPath.split('/');
            pathParts.pop();
            currentPath = pathParts.join('/');
            fetchFiles();
        }

        fetchFiles();
    </script>
</body>
</html>
