<?php
include 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert user data into the database
    $query = "INSERT INTO users (name, address, phone_number, email, password) VALUES ('$name', '$address', '$phone', '$email', '$password')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Registration successful!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --success: #00b894;
            --error: #ff7675;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(45deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
        }

        /* Animated Background Bubbles */
        .circles {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden; z-index: -1;
            margin: 0; padding: 0;
        }
        .circles li {
            position: absolute; display: block; list-style: none;
            width: 20px; height: 20px; background: rgba(255, 255, 255, 0.1);
            animation: animate 25s linear infinite; bottom: -150px;
        }
        @keyframes animate {
            0%{ transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100%{ transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

        /* Registration Card */
        .register-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            width: 400px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            text-align: center;
            color: #fff;
            margin: 20px;
        }

        h2 { margin-bottom: 25px; font-weight: 600; letter-spacing: 1px; }

        .input-group { position: relative; margin-bottom: 20px; }

        input {
            width: 100%;
            padding: 10px 0;
            background: transparent;
            border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.5);
            outline: none;
            color: #fff;
            font-size: 15px;
            transition: 0.3s;
        }

        input::placeholder { color: rgba(255,255,255,0.6); }
        input:focus { border-bottom: 2px solid var(--secondary); }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background: var(--primary);
            color: white;
            font-size: 17px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.4s;
            margin-top: 15px;
        }

        button:hover {
            background: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .msg {
            font-size: 14px;
            margin-bottom: 15px;
            padding: 8px;
            border-radius: 5px;
        }
        .success { background: rgba(0, 184, 148, 0.2); color: var(--success); }
        .error { background: rgba(255, 118, 117, 0.2); color: var(--error); }

        a { color: var(--secondary); text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

    <ul class="circles">
        <li style="left: 25%; width: 80px; height: 80px; animation-delay: 0s;"></li>
        <li style="left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s;"></li>
        <li style="left: 70%; width: 20px; height: 20px; animation-delay: 4s;"></li>
        <li style="left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s;"></li>
        <li style="left: 85%; width: 150px; height: 150px; animation-delay: 7s;"></li>
    </ul>

    <div class="register-card animate__animated animate__fadeInUp" data-tilt>
        <h2>Join Us</h2>
        
        <?php if(isset($success)) echo "<div class='msg success'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='msg error'>$error</div>"; ?>

        <form method="POST" action="register.php">
            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <input type="text" name="address" placeholder="Address" required>
            </div>
            <div class="input-group">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p style="margin-top: 20px; font-size: 13px; opacity: 0.8;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
</body>
</html>