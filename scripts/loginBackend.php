<?php
include 'pdo.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If there's no cookie, proceed with normal login process

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Check if the user exists
    $sql = "SELECT * FROM users WHERE userName='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $row['pass'])) {
            echo "Login successful. Welcome " . $username;
            // Start a session and store user info in session variables
            $_SESSION['user_id'] = $row['userID'];
            $_SESSION['username'] = $username;
            $_SESSION['userLevel'] = $row['userLevel'];
            

            header("Location: ../welcome.php"); // Redirect to welcome page after successful login
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }

    $conn->close();
}

?>
