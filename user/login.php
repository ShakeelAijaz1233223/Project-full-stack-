<?php
session_start();
include "../config/db.php"; 

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($pass, $row['password']) || $pass == $row['password']) {

            if (isset($row['status']) && $row['status'] == 'blocked') {
                $error_msg = "Your account has been blocked by Admin!";
            } else {
                $update_sql = "UPDATE users SET last_seen = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $row['id']);
                $update_stmt->execute();

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email']   = $row['email'];
                $_SESSION['role']    = $row['role'];
                $_SESSION['name']    = $row['name'];

                if ($row['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../user/index.php");
                }
                exit();
            }
        } else {
            $error_msg = "Incorrect password!";
        }
    } else {
        $error_msg = "User not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Studio Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --error: #ff7675;
            --glass: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(45deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* --- BACKGROUND BUBBLES --- */
        .circles {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden; z-index: -1; margin: 0; padding: 0;
        }
        .circles li {
            position: absolute; display: block; list-style: none;
            width: 20px; height: 20px; background: rgba(255, 255, 255, 0.1);
            animation: animateBg 25s linear infinite; bottom: -150px;
        }
        @keyframes animateBg {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

        /* --- LOGIN CARD WITH DOWN-TO-UP ANIMATION --- */
        .login-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px 40px;
            width: 380px;
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            text-align: center;
            color: #fff;
            
            /* Custom Animation Call */
            animation: slideUp 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes slideUp {
            0% {
                transform: translateY(100vh); /* Screen ke bilkul niche se start */
                opacity: 0;
            }
            100% {
                transform: translateY(0); /* Center par aakar ruk jayega */
                opacity: 1;
            }
        }

        h2 { margin-bottom: 10px; font-weight: 600; letter-spacing: 1px; }
        .subtitle { font-size: 14px; opacity: 0.7; margin-bottom: 30px; }
        .input-group { position: relative; margin-bottom: 25px; }

        input {
            width: 100%; padding: 12px 0; background: transparent;
            border: none; border-bottom: 2px solid rgba(255, 255, 255, 0.5);
            outline: none; color: #fff; font-size: 15px; transition: 0.3s;
        }
        input:focus { border-bottom: 2px solid var(--secondary); }
        input::placeholder { color: rgba(255, 255, 255, 0.5); }

        .error-msg {
            background: rgba(255, 118, 117, 0.2); color: var(--error);
            border: 1px solid rgba(255, 118, 117, 0.3);
            padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;
        }

        button {
            width: 100%; padding: 12px; border: none; border-radius: 25px;
            background: var(--primary); color: white; font-size: 17px;
            font-weight: 500; cursor: pointer; transition: 0.4s;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4);
        }
        button:hover {
            background: var(--secondary); transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .signup-link { margin-top: 25px; font-size: 13px; opacity: 0.8; }
        .signup-link a { color: var(--secondary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

    <ul class="circles">
        <li style="left: 25%; width: 80px; height: 80px;"></li>
        <li style="left: 10%; width: 20px; height: 20px; animation-delay: 2s;"></li>
        <li style="left: 70%; width: 20px; height: 20px; animation-delay: 4s;"></li>
        <li style="left: 40%; width: 60px; height: 60px; animation-duration: 18s;"></li>
        <li style="left: 85%; width: 150px; height: 150px; animation-delay: 7s;"></li>
    </ul>

    <div class="login-card" data-tilt>
        <h2>Welcome Back</h2>
        <p class="subtitle">Enter your credentials to continue</p>

        <?php if ($error_msg != ""): ?>
            <div class="error-msg animate__animated animate__shakeX">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <input type="email" name="username" placeholder="Email Address" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="register.php">Sign Up</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.7.0/vanilla-tilt.min.js"></script>
</body>
</html>