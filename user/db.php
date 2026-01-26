<?php
$servername = "localhost";
$username = "root";  // your MySQL username
$password = "";      // your MySQL password
$dbname = "dashboard_db";  // your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

// Loop through each user and update their password
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Hash the password
        $hashed_password = password_hash($row['password'], PASSWORD_DEFAULT);

        // Update the password in the database
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $row['id']);
        $stmt->execute();
    }

    echo "Passwords have been hashed and updated!";
} else {
    echo "No users found!";
}

$conn->close();
?>
