<?php
include 'scripts/pdo.php';

session_start();

if (isset($_COOKIE['auto_login'])) {
    $userID = $_COOKIE['auto_login'];
    
    // Check if the user exists
    $sql = "SELECT * FROM users WHERE userID='$userID'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Start a session and store user info in session variables
        $_SESSION['user_id'] = $row['userID'];
        $_SESSION['username'] = $row['userName'];
        $_SESSION['userLevel'] = $row['userLevel'];

        // Redirect to the welcome page after logging in
        header("Location: welcome.php"); 
        exit();
    } 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="scripts/loginBackend.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>
