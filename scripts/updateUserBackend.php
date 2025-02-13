<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
include 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfileAndSettings'])) {
    $userID = intval($_POST['userID']);
    $userName = $_POST['userName'];
    $realFirstName = $_POST['realFirstName'];
    $realLastName = $_POST['realLastName'];
    $email = $_POST['email'];
    
    // Start building the update query
    $query = "UPDATE users SET 
        userName = ?, 
        realFirstName = ?, 
        realLastName = ?, 
        email = ?";

    // Prepare parameters and types for binding
    $params = [
        $userName,
        $realFirstName,
        $realLastName,
        $email,
    ];
    $types = "ssss"; // Data types for bind_param

    // If a new password is provided, include it in the query
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", pass = ?";
        $params[] = $password; // Add the password to the parameters
        $types .= "s"; // Add the type for the password
    }

    // Append WHERE clause
    $query .= " WHERE userID = ?";
    $params[] = $userID;
    $types .= "i";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Profile and settings updated successfully.";
    } else {
        echo "Error updating profile and settings: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    // Redirect back to the original page
    header("Location: ../updateUser.php");
    exit();
}
?>
