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
    <title>Register | SOUND</title>
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <style>
        :root {
            --primary: #ff0055;
            --primary-hover: #d90049;
            --secondary: #00d4ff;
            --bg-dark: #050505;
            --bg-card: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-muted: #888888;
            --font-head: 'Syncopate', sans-serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-body);
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
            padding: 20px 0;
        }

        /* --- BACKGROUND EFFECT --- */
        .bg-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1514525253440-b393452e23f9?q=80&w=1920&auto=format&fit=crop') no-repeat center/cover;
            opacity: 0.2;
            z-index: -1;
        }

        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(5, 5, 5, 0.9), rgba(5, 5, 5, 0.7));
            z-index: -1;
        }

        /* --- FLOATING ELEMENTS --- */
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* --- REGISTER CONTAINER --- */
        .register-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            z-index: 10;
        }

        .register-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- LOGO --- */
        .logo {
            font-family: var(--font-head);
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
            letter-spacing: 3px;
        }

        .logo span {
            color: var(--primary);
        }

        /* --- FORM ELEMENTS --- */
        .subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--text-muted);
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            border-radius: 10px;
            color: var(--text-main);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 15px rgba(255, 0, 85, 0.1);
        }

        /* --- BUTTON --- */
        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            background: var(--primary-hover);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 0, 85, 0.3);
        }

        /* --- MESSAGES --- */
        .msg {
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-msg {
            background: rgba(255, 0, 85, 0.1);
            color: var(--primary);
            border: 1px solid rgba(255, 0, 85, 0.2);
        }

        .success-msg {
            background: rgba(0, 255, 127, 0.1);
            color: #00ff7f;
            border: 1px solid rgba(0, 255, 127, 0.2);
        }

        /* --- LOGIN LINK --- */
        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-hover);
        }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 768px) {
            .register-container {
                padding: 15px;
            }

            .register-card {
                padding: 30px 20px;
            }

            .logo {
                font-size: 24px;
            }

            .form-control {
                padding: 10px 12px 10px 38px;
            }

            .btn-register {
                padding: 12px;
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 25px 15px;
                border-radius: 15px;
            }

            .logo {
                font-size: 22px;
            }

            .subtitle {
                font-size: 13px;
                margin-bottom: 25px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-control {
                padding: 10px 10px 10px 35px;
                font-size: 13px;
            }

            .btn-register {
                padding: 11px;
                font-size: 12px;
            }

            .msg {
                padding: 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 360px) {
            .register-card {
                padding: 20px 12px;
            }

            .logo {
                font-size: 20px;
            }

            .form-control {
                padding: 9px 9px 9px 32px;
                font-size: 12px;
            }

            .btn-register {
                padding: 10px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-effect"></div>
    <div class="bg-overlay"></div>
    
    <div class="floating-elements">
        <div class="floating-element" style="width: 300px; height: 300px; top: -150px; right: -150px; animation-delay: 0s;"></div>
        <div class="floating-element" style="width: 200px; height: 200px; bottom: -100px; left: -100px; animation-delay: 2s;"></div>
        <div class="floating-element" style="width: 150px; height: 150px; top: 50%; left: -75px; animation-delay: 4s;"></div>
        <div class="floating-element" style="width: 100px; height: 100px; bottom: 30%; right: -50px; animation-delay: 6s;"></div>
    </div>

    <div class="register-container">
        <div class="register-card animate__animated animate__fadeIn">
            <div class="logo">SOU<span>N</span>D</div>
            <p class="subtitle">Create your account to start listening</p>

            <?php if($error): ?>
                <div class="msg error-msg animate__animated animate__shakeX"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="msg success-msg animate__animated animate__fadeIn"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="address" class="form-control" placeholder="Enter your address" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                    </div>
                </div>

                <button type="submit" class="btn-register">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </div>
    </div>
</body>
</html>