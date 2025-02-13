<?php
include 'pdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match. Please try again.";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user into the database
    $sql = "INSERT INTO users (userName, pass, email, realFirstName, realLastName) 
            VALUES ('$username', '$hashed_password', '$email', '$firstName', '$lastName')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully.";

        // Get the userID for the recently created user
        $userID = $conn->insert_id; // Get the last inserted ID
        
        // Create a user folder inside 'uploads'
        $uploadDir = __DIR__ . "/uploads/" . preg_replace("/[^a-zA-Z0-9_-]/", "_", $username); // Sanitize folder name
        if (!file_exists($uploadDir)) {
            if (mkdir($uploadDir, 0777, true)) { // Creates the directory with full permissions
                echo "User folder created successfully.";
            } else {
                echo "Error creating user folder.";
            }
        }
        
        header("Location: ../login.php"); // Redirect to login page after successful registration
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
