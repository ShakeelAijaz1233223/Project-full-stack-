<?php
session_start();  // Start a session

// Database configuration
$servername = "localhost";
$username = "root"; // your MySQL username
$password = ""; // your MySQL password
$dbname = "dashboard_db"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $user = $_POST['username'];  // Ensure the field name is correct in the form
    $pass = $_POST['password'];  // Ensure the field name is correct in the form

    // Prepare SQL statement to fetch user from database
    $sql = "SELECT * FROM users WHERE gmail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user); // 's' means the variable is a string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password using password_verify() (recommended for security)
        if (password_verify($pass, $row['password'])) {
            $_SESSION['gmail'] = $user; // Store email in session
            $_SESSION['role'] = $row['role']; // Store user role
            header("Location: index.php"); // Redirect to welcome page
            exit();
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "User not found!";
    }

    // Close connection
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="username">Email</label>
                <input type="text" id="username" name="username" placeholder="Enter your email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="submit-group">
                <button type="submit">Login</button>
            </div>
            <div class="signup-link">
                <p>Don't have an account? <a href="#">Sign Up</a></p>
            </div>
        </form>
    </div>
</body>
</html>
