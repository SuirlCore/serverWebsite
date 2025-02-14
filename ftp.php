<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FTP File Manager</title>
    <style>
        body { font-family: Arial, sans-serif; }
        #file-list { margin-top: 20px; }
        .file-item { display: flex; align-items: center; margin: 5px 0; }
        .delete-btn, .move-btn, .move-up-btn, .action-btn {
            margin-left: 10px;
            cursor: pointer;
            padding: 5px 10px;
            border: 1px solid #ccc;
            background-color: #f8f8f8;
            border-radius: 5px;
        }
        .delete-btn { color: red; }
        .move-btn { color: blue; }
        .move-up-btn { color: green; }
        #controls { margin-bottom: 10px; }
        button { padding: 8px 12px; margin-right: 5px; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>
    <h2>FTP File Manager</h2>

    <div id="controls">
        <!-- Root Selection (Only for Admins) -->
        <?php if ($_SESSION['userLevel'] == 1): ?>
            <button onclick="switchRoot()" class="action-btn">Switch Root</button>
        <?php endif; ?>
        
        <button onclick="goBack()" id="go-back" style="display: none;">Go Back</button>
        <input type="file" id="file-input">
        <button onclick="uploadFile()" class="action-btn">Upload</button>
        <button onclick="createFolder()" class="action-btn">Create Folder</button>
    </div>

    <div id="file-list"></div>

    <script>
        let currentDir = ''; // Track the current folder
        let currentRoot = 'user'; // Default to user's home directory

        function fetchFiles() {
            fetch('ftpScripts/ftpBackend.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=fetch&current_dir=${currentDir}&requested_root=${currentRoot}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const fileList = document.getElementById('file-list');
                fileList.innerHTML = '';

                data.files.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'file-item';
                    
                    const itemName = document.createElement('span');
                    itemName.textContent = item.name;
                    itemName.style.cursor = 'pointer';

                    if (item.type === 'folder') {
                        itemName.onclick = () => navigateToFolder(item.name);
                    }

                    div.appendChild(itemName);

                    if (item.type === 'file') {
                        // Delete button
                        const deleteBtn = document.createElement('button');
                        deleteBtn.textContent = 'Delete';
                        deleteBtn.className = 'delete-btn';
                        deleteBtn.onclick = () => deleteFile(item.name);
                        div.appendChild(deleteBtn);

                        // Move file button
                        const moveBtn = document.createElement('button');
                        moveBtn.textContent = 'Move File';
                        moveBtn.className = 'move-btn';
                        moveBtn.onclick = () => moveItem(item.name, 'file');
                        div.appendChild(moveBtn);

                        // Move Up file button
                        if (currentDir) {
                            const moveUpBtn = document.createElement('button');
                            moveUpBtn.textContent = 'Move Up';
                            moveUpBtn.className = 'move-up-btn';
                            moveUpBtn.onclick = () => moveUp(item.name);
                            div.appendChild(moveUpBtn);
                        }
                    } else if (item.type === 'folder') {
                        // Delete folder button
                        const deleteFolderBtn = document.createElement('button');
                        deleteFolderBtn.textContent = 'Delete Folder';
                        deleteFolderBtn.className = 'delete-btn';
                        deleteFolderBtn.onclick = () => deleteFolder(item.name);
                        div.appendChild(deleteFolderBtn);

                        // Move folder button
                        const moveBtn = document.createElement('button');
                        moveBtn.textContent = 'Move Folder';
                        moveBtn.className = 'move-btn';
                        moveBtn.onclick = () => moveItem(item.name, 'folder');
                        div.appendChild(moveBtn);

                        // Move Up folder button
                        if (currentDir) {
                            const moveUpBtn = document.createElement('button');
                            moveUpBtn.textContent = 'Move Up';
                            moveUpBtn.className = 'move-up-btn';
                            moveUpBtn.onclick = () => moveUp(item.name);
                            div.appendChild(moveUpBtn);
                        }
                    }

                    fileList.appendChild(div);
                });

                document.getElementById('go-back').style.display = currentDir ? 'inline' : 'none';
            })
            .catch(error => console.error('Error fetching files:', error));
        }

        function switchRoot() {
            currentRoot = (currentRoot === 'user') ? 'server' : 'user';
            currentDir = ''; // Reset to root of the selected directory
            fetchFiles();
        }

        function navigateToFolder(folderName) {
            currentDir = currentDir ? `${currentDir}/${folderName}` : folderName;
            fetchFiles();
        }

        function goBack() {
            const pathArray = currentDir.split('/');
            pathArray.pop();
            currentDir = pathArray.join('/');
            fetchFiles();
        }

        function uploadFile() {
            const fileInput = document.getElementById('file-input');
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('file', fileInput.files[0]);
            formData.append('current_dir', currentDir);
            formData.append('requested_root', currentRoot);

            fetch('ftpScripts/ftpBackend.php', { method: 'POST', body: formData })
                .then(() => fetchFiles());
        }

        function deleteFile(file) {
            if (confirm(`Delete ${file}?`)) {
                fetch('ftpScripts/ftpBackend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&file=${file}&current_dir=${currentDir}&requested_root=${currentRoot}`
                }).then(() => fetchFiles());
            }
        }

        function deleteFolder(folder) {
            if (confirm(`Delete folder ${folder}?`)) {
                fetch('ftpScripts/ftpBackend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete_folder&folder=${folder}&current_dir=${currentDir}&requested_root=${currentRoot}`
                }).then(() => fetchFiles());
            }
        }

        function createFolder() {
            const folderName = prompt('Enter folder name:');
            if (folderName) {
                fetch('ftpScripts/ftpBackend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=create_folder&folder=${folderName}&current_dir=${currentDir}&requested_root=${currentRoot}`
                }).then(() => fetchFiles());
            }
        }

        function moveItem(itemName, itemType) {
            const destination = prompt('Enter the destination folder name:');
            if (destination) {
                fetch('ftpScripts/ftpBackend.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=move&source=${itemName}&destination=${destination}&current_dir=${currentDir}&requested_root=${currentRoot}&type=${itemType}`
                }).then(() => fetchFiles());
            }
        }

        function moveUp(itemName) {
            const parentDir = currentDir.split('/').slice(0, -1).join('/');
            fetch('ftpScripts/ftpBackend.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=move_up&source=${itemName}&current_dir=${currentDir}&parent_dir=${parentDir}&requested_root=${currentRoot}`
            }).then(() => fetchFiles());
        }

        document.addEventListener('DOMContentLoaded', fetchFiles);
    </script>
</body>
</html>
