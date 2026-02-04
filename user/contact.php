<?php
session_start();
include_once("../config/db.php");
$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $user_msg = strip_tags(trim($_POST['message']));

    // Yahan aap apna database insert ya email logic daal sakte hain
    $message_status = "<p style='color: #00ff7f; background: rgba(0,255,127,0.1); padding: 15px; border-radius: 12px; margin-bottom: 25px; text-align: center; border: 1px solid rgba(0,255,127,0.3); font-weight: 600; font-size: 13px;'>Message Sent Successfully!</p>";
}

// User check for Header consistency
$user = null;
if (isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | SOUND 2026</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #ff0055;
            --bg-dark: #050505;
            --border-glass: rgba(255, 255, 255, 0.1);
            --card-bg: rgba(255, 255, 255, 0.02);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
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
        }

        /* --- SYNCED HEADER (Matches Home/About) --- */
        header {
            background: rgba(5, 5, 5, 0.9);
            backdrop-filter: blur(20px);
            padding: 18px 5%;
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
            font-family: 'Syncopate', sans-serif;
            font-size: clamp(16px, 4vw, 22px);
            color: #fff;
            text-decoration: none;
            letter-spacing: 5px;
        }

        .logo span {
            color: var(--primary);
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        nav ul li a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            transition: 0.3s;
            letter-spacing: 1px;
        }

        nav ul li a:hover {
            color: var(--primary);
        }

        /* --- USER DROPDOWN --- */
        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            background: rgba(255, 255, 255, 0.05);
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            border: 1px solid var(--border-glass);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
        }

        .dropdown-content {
            position: absolute;
            right: 0;
            top: 55px;
            background: rgba(15, 15, 17, 0.98);
            backdrop-filter: blur(25px);
            min-width: 200px;
            border-radius: 18px;
            padding: 10px;
            border: 1px solid var(--border-glass);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--transition);
        }

        .user-dropdown:hover .dropdown-content {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-content a {
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            font-size: 12px;
            border-radius: 10px;
        }

        .dropdown-content a:hover {
            background: rgba(255, 0, 85, 0.1);
        }

        /* --- CONTACT CONTENT --- */
        .page-container {
            min-height: 100vh;
            padding: 140px 8% 50px;
        }

        .hero-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .hero-title h1 {
            font-family: 'Syncopate';
            font-size: clamp(2.2rem, 6vw, 4rem);
            text-transform: uppercase;
        }

        .hero-title h1 span {
            color: var(--primary);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: start;
        }

        .info-text h2 {
            font-family: 'Syncopate';
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 20px;
        }

        .info-text p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .contact-item i {
            width: 45px;
            height: 45px;
            background: rgba(255, 0, 85, 0.1);
            border: 1px solid rgba(255, 0, 85, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--primary);
        }

        .form-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 30px;
            border: 1px solid var(--border-glass);
            backdrop-filter: blur(10px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: var(--primary);
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-glass);
            padding: 16px;
            border-radius: 15px;
            color: #fff;
            outline: none;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
        }

        .send-btn {
            width: 100%;
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 18px;
            border-radius: 15px;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.4s;
        }

        .send-btn:hover {
            box-shadow: 0 10px 30px rgba(255, 0, 85, 0.3);
            transform: translateY(-3px);
        }

        footer {
            padding: 40px;
            text-align: center;
            border-top: 1px solid var(--border-glass);
            opacity: 0.4;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 50px;
            }

            .hero-title {
                margin-bottom: 40px;
            }
        }
        
    </style>
</head>

<body>
    <header>
        <a href="index.php" class="logo">SOU<span>N</span>D</a>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="user_music_view.php">Music</a></li>
                <li><a href="user_video_view.php">Videos</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php" style="color:var(--primary)">Contact</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <?php if ($user): ?>
                <div class="user-dropdown">
                    <div class="user-trigger">
                        <div style="width: 25px; height: 25px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
                            <?= strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span style="font-size: 12px; font-weight: 700;"><?= htmlspecialchars($user['name']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 9px; opacity: 0.5;"></i>
                    </div>
                    <div class="dropdown-content">
                        <a href="user_setting.php"><i class="fas fa-cog"></i> Settings</a>
                        <div style="height: 1px; background: var(--border-glass); margin: 5px 0;"></div>
                        <a href="user_logout.php" style="color: #ff4d4d;"><i class="fas fa-power-off"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" style="background: var(--primary); padding: 8px 22px; border-radius: 30px; text-decoration: none; color: white; font-size: 11px; font-weight: 800; transition: 0.3s;">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="page-container">
        <div class="hero-title animate__animated animate__fadeInDown">
            <h1>GET IN <span>TOUCH</span></h1>
        </div>

        <div class="content-grid">
            <div class="info-text animate__animated animate__fadeInLeft">
                <h2>WANT TO TALK?</h2>
                <p>Have a question or feedback? We're here to help you experience music like never before. Reach out and our team will get back to you within 24 hours.</p>

                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <p style="font-size: 10px; color: var(--primary); font-weight: 800;">EMAIL US</p>
                        <p style="font-weight: 600;">support@sound2026.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <p style="font-size: 10px; color: var(--primary); font-weight: 800;">VISIT US</p>
                        <p style="font-weight: 600;">Digital Plaza, Silicon Valley, CA</p>
                    </div>
                </div>
            </div>

            <div class="form-card animate__animated animate__fadeInRight">
                <?php echo $message_status; ?>
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="name@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Your Message</label>
                        <textarea name="message" rows="5" placeholder="Tell us something..." required></textarea>
                    </div>
                    <button type="submit" class="send-btn">SEND MESSAGE</button>
                </form>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 SOUND PORTAL | ALL RIGHTS RESERVED</footer>
</body>

</html>