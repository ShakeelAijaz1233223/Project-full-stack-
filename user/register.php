<?php
session_start();
include_once("../config/db.php");

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $address  = mysqli_real_escape_string($conn, $_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        $error = "This email is already registered!";
    } else {
        $query = "INSERT INTO users (name, email, phone, address, password, status) 
                  VALUES ('$name', '$email', '$phone', '$address', '$password', 'active')";
        if (mysqli_query($conn, $query)) {
            $success = "Account created! Redirecting to login...";
            header("refresh:2;url=login.php");
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SOUND Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --error: #ff7675;
            --glass: rgba(255, 255, 255, 0.1);
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

        /* --- BACKGROUND ANIMATION (CIRCLES) --- */
        .circles {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden; z-index: -1; margin: 0; padding: 0;
        }
        .circles li {
            position: absolute; display: block; list-style: none;
            width: 20px; height: 20px; background: rgba(255, 255, 255, 0.1);
            animation: animateBg 25s linear infinite; bottom: -150px;
        }
        .circles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .circles li:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .circles li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
        .circles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        @keyframes animateBg {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

        /* --- REGISTER CARD WITH UP-TO-DOWN ANIMATION --- */
        .register-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            width: 400px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            text-align: center;
            color: #fff;
            z-index: 10;
            
            /* Slide Down Animation */
            animation: slideDown 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes slideDown {
            0% {
                transform: translateY(-100vh); /* Screen ke upar se start hoga */
                opacity: 0;
            }
            100% {
                transform: translateY(0); /* Center par ruk jayega */
                opacity: 1;
            }
        }

        h2 { margin-bottom: 5px; font-weight: 600; letter-spacing: 1px; }
        .subtitle { font-size: 13px; opacity: 0.7; margin-bottom: 25px; }
        .input-group { position: relative; margin-bottom: 20px; text-align: left; }
        
        input {
            width: 100%; padding: 10px 0; background: transparent; border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3); outline: none;
            color: #fff; font-size: 14px; transition: 0.3s;
        }
        input::placeholder { color: rgba(255, 255, 255, 0.5); }
        input:focus { border-bottom: 2px solid var(--secondary); }

        .msg { padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; }
        .error-msg { background: rgba(255, 118, 117, 0.2); color: var(--error); border: 1px solid rgba(255, 118, 117, 0.3); }
        .success-msg { background: rgba(85, 239, 196, 0.2); color: #55efc4; border: 1px solid rgba(85, 239, 196, 0.3); }

        button {
            width: 100%; padding: 12px; border: none; border-radius: 25px;
            background: var(--primary); color: white; font-size: 16px; font-weight: 500;
            cursor: pointer; transition: 0.4s; margin-top: 10px;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4);
        }
        button:hover {
            background: var(--secondary); transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .login-link { margin-top: 25px; font-size: 13px; opacity: 0.8; }
        .login-link a { color: var(--secondary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

    <ul class="circles">
        <li></li><li></li><li></li><li></li><li></li>
    </ul>

    <div class="register-card">
        <h2>Join SOUND</h2>
        <p class="subtitle">Create your account to start listening</p>

        <?php if($error): ?>
            <div class="msg error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="msg success-msg"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="input-group">
                <input type="text" name="address" placeholder="Address" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Create Password" required>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>

</body>
</html>