<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apache on Odin</title>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <h1>
        Welcome Page
    </h1>
    
    <p>
        stuff goes here
    </p>
</body>
</html>
