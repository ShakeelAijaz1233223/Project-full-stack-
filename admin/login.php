<?php
session_start();
include "../config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin_users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Plain text check (As per your current logic)
        if ($password === $user['password']) {
            if ($user['status'] === 'active') {
                
                // --- UPDATE LAST SEEN ON LOGIN ---
                mysqli_query($conn, "UPDATE admin_users SET last_seen = NOW() WHERE id = " . $user['id']);
                
                $_SESSION['email'] = $user['email'];
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Access Denied! Your account is blocked.";
            }
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Music Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0f0c29;
            background: linear-gradient(to right, #24243e, #302b63, #0f0c29);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
        }

        /* Animated Background Bubbles */
        .bubbles {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;
        }
        .bubbles li {
            position: absolute; list-style: none; display: block; width: 20px; height: 20px;
            background: rgba(255, 255, 255, 0.1); animation: animate 25s linear infinite; bottom: -150px;
        }
        @keyframes animate {
            0%{ transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100%{ transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            z-index: 2;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            transition: 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .login-card h3 {
            color: #fff;
            font-weight: 600;
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        .login-card h3 i {
            color: #e14eca;
            margin-right: 10px;
        }

        .form-label { color: rgba(255, 255, 255, 0.8); font-size: 0.9rem; margin-left: 5px; }

        .input-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .input-group:focus-within {
            border-color: #e14eca;
            box-shadow: 0 0 10px rgba(225, 78, 202, 0.3);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #e14eca;
        }

        .form-control {
            background: transparent;
            border: none;
            color: #fff;
            padding: 12px 10px;
        }

        .form-control:focus {
            background: transparent;
            box-shadow: none;
            color: #fff;
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: #fff;
            width: 100%;
            margin-top: 10px;
            transition: 0.4s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: scale(1.02);
            filter: brightness(1.1);
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
        }

        .alert-custom {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff99a2;
            font-size: 0.85rem;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    <ul class="bubbles">
        <li style="left: 25%; width: 80px; height: 80px;"></li>
        <li style="left: 10%; width: 20px; height: 20px; animation-delay: 2s;"></li>
        <li style="left: 70%; width: 20px; height: 20px; animation-delay: 4s;"></li>
        <li style="left: 40%; width: 60px; height: 60px; animation-duration: 18s;"></li>
        <li style="left: 65%; width: 20px; height: 20px; animation-delay: 0s;"></li>
    </ul>

    <div class="login-card">
        <h3><i class="fa-solid fa-compact-disc fa-spin"></i> ADMIN LOGIN</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-custom mb-4 p-2">
                <i class="fa fa-circle-exclamation me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login">
                Sign In <i class="fa fa-arrow-right-to-bracket ms-2"></i>
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>