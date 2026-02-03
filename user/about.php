<?php
session_start();
include_once("../config/db.php");

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
    <title>About | SOUND 2026</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #ff0055;
            --bg-dark: #050505;
            --border-glass: rgba(255, 255, 255, 0.1);
            --card-glass: rgba(255, 255, 255, 0.03);
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
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* --- SYNCED HEADER (Matches Home/Contact) --- */
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

        .logo span { color: var(--primary); }

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

        nav ul li a:hover { color: var(--primary); }

        /* --- SYNCED USER DROPDOWN (Matches Home/Contact) --- */
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

        .user-dropdown { position: relative; }
        
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
            font-weight: 600;
            border-radius: 10px;
        }

        .dropdown-content a:hover { background: rgba(255, 0, 85, 0.1); }

        /* --- ABOUT PAGE SPECIFIC CONTENT --- */
        .about-hero {
            height: 55vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(rgba(0, 0, 0, 0.7), var(--bg-dark)), url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        .about-content {
            padding: 80px 15%;
            text-align: center;
        }

        .about-content h2 {
            font-family: 'Syncopate';
            color: var(--primary);
            margin-bottom: 30px;
            font-size: 2rem;
        }

        .about-content p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 50px;
        }

        .stat-box {
            background: var(--card-glass);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid var(--border-glass);
            transition: 0.4s;
        }

        .stat-box:hover {
            border-color: var(--primary);
            transform: translateY(-10px);
        }

        .stat-box h3 {
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 10px;
        }

        .stat-box p {
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--primary);
        }

        footer {
            padding: 40px;
            text-align: center;
            border-top: 1px solid var(--border-glass);
            margin-top: 50px;
            opacity: 0.5;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            .stats { grid-template-columns: 1fr; }
            .about-content { padding: 80px 5%; }
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
                <li><a href="about.php" style="color:var(--primary)">About</a></li>
                <li><a href="contact.php">Contact</a></li>
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

    <section class="about-hero">
        <h1 class="animate__animated animate__fadeInDown" style="font-family:'Syncopate'; font-size: clamp(2.5rem, 8vw, 4rem);">OUR STORY</h1>
    </section>

    <section class="about-content animate__animated animate__fadeInUp">
        <h2>REDEFINING AUDIO</h2>
        <p>SOUND 2026 was born out of a passion for pure, unadulterated sound. We believe that music is not just background noise; it is an experience that should be felt in every fiber of your being.</p>

        <div class="stats">
            <div class="stat-box">
                <h3>10M+</h3>
                <p>Active Listeners</p>
            </div>
            <div class="stat-box">
                <h3>50K+</h3>
                <p>Artists</p>
            </div>
            <div class="stat-box">
                <h3>100%</h3>
                <p>High Fidelity</p>
            </div>
        </div>
    </section>

    <footer>&copy; 2026 SOUND PORTAL | ALL RIGHTS RESERVED</footer>
</body>
</html>