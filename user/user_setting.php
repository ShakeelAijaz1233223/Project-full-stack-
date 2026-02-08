<?php
session_start();
include_once("../config/db.php");

// Redirect to login if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

 $email = $_SESSION['email'];
 $message = "";
 $error = "";

// Handle Password Change Only
if (isset($_POST['change_password'])) {
    $current_pw = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_pw = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_pw = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Fetch user to verify current password
    $res = mysqli_query($conn, "SELECT password FROM users WHERE email='$email' LIMIT 1");
    $user = mysqli_fetch_assoc($res);

    if ($current_pw != $user['password']) {
        $error = "The current password you entered is incorrect.";
    } elseif ($new_pw != $confirm_pw) {
        $error = "New passwords do not match. Please try again.";
    } elseif (strlen($new_pw) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $update_pw = "UPDATE users SET password='$new_pw' WHERE email='$email'";
        if (mysqli_query($conn, $update_pw)) {
            $message = "Your password has been updated successfully!";
        } else {
            $error = "System error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings | SOUND</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Syncopate:wght@700&display=swap" rel="stylesheet">
    
    <style>
      :root {
    --primary: #ff0055;
    --bg-dark: #050505;
    --card-bg: rgba(255, 255, 255, 0.03);
    --border-glass: rgba(255, 255, 255, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

body {
    background-color: var(--bg-dark);
    color: #fff;
    overflow-x: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 60px 10px 20px; /* top padding for fixed header */
}

/* Navigation Header */
header {
    background: rgba(5, 5, 5, 0.8);
    backdrop-filter: blur(15px);
    padding: 15px 5%;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-glass);
}
.logo {
    font-family: 'Syncopate';
    font-size: 20px;
    color: #fff;
    text-decoration: none;
    letter-spacing: 3px;
}
.logo span {
    color: var(--primary);
}

/* Container */
.wrapper {
    width: 100%;
    max-width: 500px;
    padding: 20px;
}

/* Security Card */
.security-card {
    background: var(--card-bg);
    border-radius: 30px;
    padding: 30px 20px;
    border: 1px solid var(--border-glass);
    backdrop-filter: blur(10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    width: 100%;
    box-sizing: border-box;
}

/* Header Text */
.header-text {
    text-align: center;
    margin-bottom: 30px;
}
.header-text i {
    font-size: 36px;
    color: var(--primary);
    margin-bottom: 10px;
}
.header-text h2 {
    font-family: 'Syncopate';
    font-size: 18px;
    letter-spacing: 2px;
}

/* Form Elements */
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 8px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 1px;
}

.input-wrapper {
    position: relative;
}
.input-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
    font-size: 14px;
}

input {
    width: 100%;
    padding: 12px 15px 12px 40px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border-glass);
    border-radius: 15px;
    color: #fff;
    transition: 0.3s;
    font-size: 14px;
}
input:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255,255,255,0.08);
    box-shadow: 0 0 15px rgba(255, 0, 85, 0.1);
}

/* Button */
.btn-submit {
    width: 100%;
    padding: 14px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.4s;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 13px;
}
.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 0, 85, 0.3);
}

/* Alerts */
.alert {
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 13px;
    text-align: center;
    font-weight: 600;
}
.success {
    background: rgba(0, 255, 127, 0.1);
    color: #00ff7f;
    border: 1px solid rgba(0, 255, 127, 0.2);
}
.error {
    background: rgba(255, 0, 85, 0.1);
    color: var(--primary);
    border: 1px solid rgba(255, 0, 85, 0.2);
}

.back-link {
    text-align: center;
    margin-top: 20px;
}
.back-link a {
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 12px;
    transition: 0.3s;
}
.back-link a:hover {
    color: #fff;
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 768px) {
    body {
        padding: 55px 15px 20px;
    }
    
    header {
        padding: 12px 5%;
    }
    
    .logo {
        font-size: 18px;
    }
    
    .security-card {
        padding: 25px 15px;
        border-radius: 25px;
    }

    .header-text i {
        font-size: 32px;
    }

    .header-text h2 {
        font-size: 16px;
    }

    input {
        padding: 11px 13px 11px 38px;
        font-size: 13px;
    }

    .btn-submit {
        padding: 13px;
        font-size: 12px;
    }
}

@media (max-width: 600px) {
    body {
        padding: 50px 10px 20px;
    }
    
    .security-card {
        padding: 25px 15px;
        border-radius: 20px;
    }

    .header-text i {
        font-size: 30px;
    }

    .header-text h2 {
        font-size: 16px;
    }

    input {
        padding: 10px 12px 10px 38px;
        font-size: 13px;
    }

    .btn-submit {
        padding: 12px;
        font-size: 12px;
    }
    
    .form-group {
        margin-bottom: 18px;
    }
    
    .header-text {
        margin-bottom: 25px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 45px 10px 20px;
    }
    
    header {
        padding: 10px 5%;
    }
    
    .logo {
        font-size: 16px;
        letter-spacing: 2px;
    }
    
    .wrapper {
        padding: 15px;
    }

    .security-card {
        padding: 20px 15px;
        border-radius: 18px;
    }

    .header-text i {
        font-size: 28px;
    }

    .header-text h2 {
        font-size: 15px;
        letter-spacing: 1px;
    }

    input {
        padding: 10px 10px 10px 35px;
        font-size: 12px;
    }

    .btn-submit {
        padding: 11px;
        font-size: 11px;
        letter-spacing: 1px;
    }
    
    .form-group label {
        font-size: 10px;
        margin-bottom: 6px;
    }
    
    .alert {
        padding: 10px;
        font-size: 12px;
    }
    
    .back-link a {
        font-size: 11px;
    }
}

@media (max-width: 360px) {
    body {
        padding: 40px 8px 20px;
    }
    
    header {
        padding: 8px 5%;
    }
    
    .logo {
        font-size: 14px;
    }
    
    .wrapper {
        padding: 10px;
    }

    .security-card {
        padding: 18px 12px;
        border-radius: 15px;
    }

    .header-text i {
        font-size: 24px;
    }

    .header-text h2 {
        font-size: 14px;
    }

    input {
        padding: 9px 9px 9px 32px;
        font-size: 11px;
    }

    .btn-submit {
        padding: 10px;
        font-size: 10px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .header-text {
        margin-bottom: 20px;
    }
    
    .input-wrapper i {
        font-size: 12px;
        left: 10px;
    }
}

    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">SOU<span>N</span>D</a>
        <a href="index.php" style="color:white; text-decoration:none; font-size:12px; font-weight:700;"><i class="fas fa-times"></i> CANCEL</a>
    </header>

    <div class="wrapper animate__animated animate__zoomIn">
        
        <div class="security-card">
            <div class="header-text">
                <i class="fas fa-shield-halved"></i>
                <h2>CHANGE PASSWORD</h2>
            </div>

            <?php if($message) echo "<div class='alert success animate__animated animate__shakeX'>$message</div>"; ?>
            <?php if($error) echo "<div class='alert error animate__animated animate__headShake'>$error</div>"; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="current_password" placeholder="Verify your current password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key"></i>
                        <input type="password" name="new_password" placeholder="At least 6 characters" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-check-double"></i>
                        <input type="password" name="confirm_password" placeholder="Re-type new password" required>
                    </div>
                </div>

                <button type="submit" name="change_password" class="btn-submit">
                    Update Security
                </button>
            </form>

            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Return to Dashboard</a>
            </div>
        </div>
    </div>

</body>
</html>