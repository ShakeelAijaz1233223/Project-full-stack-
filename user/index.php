<?php
session_start();
// Database connection assumption
include_once("../config/db.php");

/**
 * PATH FIXER HELPER
 * Ensures images load even if paths are broken, using high-quality placeholders.
 */
function getMediaImage($fileName, $type)
{
    // Define base paths
    $musicPath = "../admin/uploads/music_covers/";
    $videoPath = "../admin/uploads/video_thumbnails/";

    if ($type == 'music') {
        $fullPath = $musicPath . $fileName;
        // Fallback for music
        $default = "https://images.unsplash.com/photo-1614613535308-eb5fbd3d2c17?q=80&w=600&auto=format&fit=crop";
    } else {
        $fullPath = $videoPath . $fileName;
        // Fallback for video
        $default = "https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=600&auto=format&fit=crop";
    }

    // Check if file exists on server, else return placeholder
    // Note: file_exists checks local server path. If testing locally without files, it returns default.
    return (!empty($fileName) && file_exists($fullPath)) ? $fullPath : $default;
}

// --- DATA FETCHING ---

// 1. Latest Music (Requirement: 5 items)
$latestMusicQuery = "SELECT * FROM music ORDER BY id DESC LIMIT 5";
$latestMusic = isset($conn) ? mysqli_query($conn, $latestMusicQuery) : false;

// 2. Latest Videos (Requirement: 5 items)
$latestVideosQuery = "SELECT * FROM videos ORDER BY id DESC LIMIT 5";
$latestVideos = isset($conn) ? mysqli_query($conn, $latestVideosQuery) : false;

// 3. User Session Check
$user = null;
if (isset($_SESSION['email']) && isset($conn)) {
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
    <title>SOUND | The Ultimate Entertainment Hub</title>

    <!-- Icons & Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Animate.css for entrance animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        :root {
            --primary: #ff0055;
            /* Neon Pink */
            --primary-hover: #d90049;
            --secondary: #00d4ff;
            /* Cyan */
            --bg-dark: #050505;
            /* Deep Black */
            --bg-card: #0f0f0f;
            /* Dark Grey */
            --text-main: #ffffff;
            --text-muted: #888888;
            --glass: rgba(255, 255, 255, 0.05);
            --border-glass: rgba(255, 255, 255, 0.1);
            --font-head: 'Syncopate', sans-serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
        }

        /* --- GLOBAL RESET --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: var(--font-body);
            overflow-x: hidden;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
        }

        ul {
            list-style: none;
        }

        /* --- UTILITIES --- */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 5%;
        }

        .section-padding {
            padding: 100px 0;
        }

        .text-center {
            text-align: center;
        }

        .gradient-text {
            background: linear-gradient(to right, #fff, #888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .highlight {
            color: var(--primary);
        }

        /* --- MOBILE MENU BUTTON --- */
        .menu-btn {
            display: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        /* --- USER DROPDOWN --- */
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

        .user-dropdown {
            position: relative;
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
            z-index: 1001;
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
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.3s;
        }

        .dropdown-content a i {
            color: var(--primary);
            width: 15px;
        }

        .dropdown-content a:hover {
            background: rgba(255, 0, 85, 0.1);
            transform: translateX(5px);
        }


        /* Flashing Animation for New Items (Requirement) */
        @keyframes flash {
            0% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.1);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .flash-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
            z-index: 10;
            animation: flash 2s infinite;
            box-shadow: 0 0 10px var(--primary);
        }

        /* Scroll Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- HEADER --- */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(5, 5, 5, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-glass);
            padding: 20px 0;
            transition: 0.3s;
        }

        .nav-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-head);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .logo span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.7);
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .user-btn {
            padding: 8px 20px;
            background: var(--glass);
            border: 1px solid var(--border-glass);
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-btn:hover {
            background: var(--primary);
            border-color: var(--primary);
        }

        /* --- HERO SECTION --- */
        .hero {
            min-height: calc(100vh - 80px);
            margin-top: 70px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('https://images.unsplash.com/photo-1514525253440-b393452e23f9?q=80&w=1920&auto=format&fit=crop') no-repeat center / cover;
        }


        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
            font-family: sans-serif;
        }

        #animated-text {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            border-right: 3px solid #fff;
            /* Cursor effect */
            padding-right: 5px;
            animation: blink-cursor 0.7s steps(1) infinite;
        }

        @keyframes blink-cursor {

            0%,
            50%,
            100% {
                border-color: transparent;
            }

            25%,
            75% {
                border-color: #fff;
            }
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: url("bgimage.png") center / cover no-repeat;
            z-index: 1;
            ;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
        }

        .hero-subtitle {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: var(--secondary);
            margin-bottom: 20px;
            display: block;
        }

        .hero-title {
            font-family: var(--font-head);
            font-size: clamp(40px, 8vw, 80px);
            line-height: 1.1;
            margin-bottom: 30px;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .hero-desc {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .cta-group {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .btn {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 2px;
            transition: 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 10px 20px rgba(255, 0, 85, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 0, 85, 0.5);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .btn-outline:hover {
            background: #fff;
            color: #000;
        }

        /* --- ABOUT / MISSION (From Doc) --- */
        .about-section {
            position: relative;
            background: #080808;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-img {
            width: 100%;
            height: 500px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--border-glass);
        }

        .about-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .about-img:hover img {
            transform: scale(1.05);
        }

        .section-header h2 {
            font-family: var(--font-head);
            font-size: 32px;
            margin-bottom: 20px;
        }

        .section-header p {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 20px;
            font-size: 15px;
        }

        /* --- FEATURES GRID (From Problem Statement) --- */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .feature-box {
            background: var(--glass);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border-glass);
            transition: 0.3s;
        }

        .feature-box:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 30px;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .feature-box h4 {
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .feature-box p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* --- MUSIC & VIDEO CARDS --- */
        .media-scroller {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }

        .media-card {
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            transition: 0.4s;
            position: relative;
            
        }

        .media-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .card-img {
            position: relative;
            aspect-ratio: 1/1;
            overflow: hidden;
        }

        .card-img.video {
            aspect-ratio: 16/9;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .media-card:hover .card-img img {
            transform: scale(1.1);
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
        }

        .media-card:hover .play-overlay {
            opacity: 1;
        }

        .play-btn {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            box-shadow: 0 0 20px var(--primary);
        }

        .card-info {
            padding: 15px;
        }

        .card-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-meta {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
        }

        /* --- STATS SECTION --- */
        .stats-section {
            background: linear-gradient(to right, #111, #050505);
            border-top: 1px solid var(--border-glass);
            border-bottom: 1px solid var(--border-glass);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat-item h3 {
            font-family: var(--font-head);
            font-size: 40px;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .stat-item p {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        /* --- FOOTER --- */
        footer {
            background: #020202;
            padding-top: 80px;
            border-top: 1px solid var(--border-glass);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 40px;
            margin-bottom: 60px;
        }

        .footer-brand p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 20px;
            line-height: 1.6;
            max-width: 300px;
        }

        .footer-col h4 {
            font-family: var(--font-head);
            font-size: 14px;
            margin-bottom: 25px;
        }

        .footer-col ul li {
            margin-bottom: 15px;
        }

        .footer-col ul li a {
            color: var(--text-muted);
            font-size: 13px;
        }

        .footer-col ul li a:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .newsletter input {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            color: #fff;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .copyright {
            text-align: center;
            padding: 20px;
            border-top: 1px solid var(--border-glass);
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Responsive */
        @media (max-width: 992px) {

            .about-grid,
            .footer-grid,
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-title {
                font-size: 40px;
            }

            .nav-links {
                display: none;
            }

            /* Add mobile toggle if needed */
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header id="header">
        <div class="container nav-wrapper">
            <a href="index.php" class="logo">SOU<span>N</span>D</a>

            <nav class="nav-links">
                <a href="#home" class="active">Home</a>
                <a href="about.php">About</a>
                <a href="user_music_view.php">Music</a>
                <a href="user_video_view.php">Videos</a>
                <a href="user_albums_view.php">Albums</a>
                <a href="#features">Features</a>
                <a href="contact.php">Contact</a>
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
        </div>
    </header>

    <!-- 1. HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-content animate__animated animate__fadeInUp">
            <span class="hero-subtitle">Welcome to the SOUND </span>
            <h1 class="hero-title">
                <span id="animated-text"></span>
            </h1>
            <p class="hero-desc">
                The thirst for learning meeting the rhythm of life. <br>
                Stream. Review. Rate. Experience entertainment like never before.
            </p>
            <div class="cta-group">
                <button class="btn btn-primary" onclick="location.href='#music'">Start Listening</button>
                <button class="btn btn-outline" onclick="location.href='user_music_view.php'">Learn More</button>
            </div>
        </div>
    </section>

    <!-- 2. STATS BAR -->
    <section class="section-padding stats-section">
        <div class="container">
            <div class="stats-grid text-center reveal">
                <div class="stat-item">
                    <h3>20k+</h3>
                    <p>Tracks Uploaded</p>
                </div>
                <div class="stat-item">
                    <h3>5k+</h3>
                    <p>Music Videos</p>
                </div>
                <div class="stat-item">
                    <h3>150+</h3>
                    <p>Top Artists</p>
                </div>
                <div class="stat-item">
                    <h3>4.9</h3>
                    <p>User Rating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. ABOUT & MISSION (From Doc) -->
    <section class="section-padding about-section" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-text reveal">
                    <div class="section-header">
                        <h4 style="color:var(--primary); letter-spacing:2px; margin-bottom:10px;">WHO WE ARE</h4>
                        <h2>BRIDGE THE GAP</h2>
                        <p>
                            The thirst for learning, upgrading technical skills, and applying concepts in a real-life environment is what the industry demands today. However, busy schedules and far-flung locations pose barriers.
                        </p>
                        <p>
                            SOUND is the answer. An electronic, live juncture that allows you to practice step-by-step. We are revolutionizing the way you consume and rate entertainment.
                        </p>
                    </div>
                    <ul style="margin-top:20px; color:white; line-height:2;">
                        <li><i class="fas fa-check-circle highlight"></i> Real-life Project Implementation</li>
                        <li><i class="fas fa-check-circle highlight"></i> Regional & English Content</li>
                        <li><i class="fas fa-check-circle highlight"></i> User Ratings & Reviews</li>
                    </ul>
                </div>
                <div class="about-img reveal">
                    <img src="https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=1920&auto=format&fit=crop" alt="About Sound">
                    <div style="position:absolute; bottom:20px; left:20px; background:rgba(0,0,0,0.8); padding:15px; border-left:4px solid var(--primary);">
                        <h4 style="color:white; margin:0;">SINCE 2026</h4>
                        <small style="color:#aaa;">The New Era of Music</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. LATEST MUSIC (Requirement: 5 items) -->
    <section class="section-padding" id="music">
        <div class="container">
            <div class="section-header text-center reveal" style="margin-bottom:60px;">
                <span style="color:var(--primary); font-weight:700;">FRESH DROPS</span>
                <h2>LATEST MUSIC RELEASES</h2>
                <p>Top 5 trending tracks added to our library.</p>
            </div>

            <div class="media-scroller reveal">
                <?php
                if ($latestMusic && mysqli_num_rows($latestMusic) > 0) {
                    while ($row = mysqli_fetch_assoc($latestMusic)) {
                        $img = getMediaImage($row['cover_image'] ?? 'default.jpg', 'music');
                ?>
                        <div class="media-card">
                            <!-- Flashing Badge for New Additions -->
                            <div class="flash-badge">NEW</div>

                            <div class="card-img">
                                <img src="<?= $img ?>" alt="Cover">
                                <a href="user_music_view.php?id=<?= $row['id'] ?>" class="play-overlay">
                                    <div class="play-btn"><i class="fas fa-play"></i></div>
                                </a>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title"><?= htmlspecialchars($row['title'] ?? 'Unknown Track') ?></h3>
                                <div class="card-meta">
                                    <span><i class="fas fa-microphone"></i> <?= htmlspecialchars($row['artist'] ?? 'Artist') ?></span>
                                    <span><i class="fas fa-star" style="color:gold;"></i> 4.5</span>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    // FALLBACK DATA FOR PREVIEW IF DB EMPTY
                    for ($i = 1; $i <= 5; $i++) {
                    ?>
                        <div class="media-card">
                            <div class="flash-badge">NEW</div>
                            <div class="card-img">
                                <img src="https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=600&auto=format&fit=crop" alt="Cover">
                                <div class="play-overlay">
                                    <div class="play-btn"><i class="fas fa-play"></i></div>
                                </div>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title">Demo Track #<?= $i ?></h3>
                                <div class="card-meta"><span>Artist Name</span><span>2026</span></div>
                            </div>
                        </div>
                <?php }
                } ?>
            </div>

            <div class="text-center" style="margin-top:40px;">
                <a href="user_music_view.php" class="btn btn-outline" style="border-radius:4px; font-size:11px;">VIEW ALL LIBRARY</a>
            </div>
        </div>
    </section>

    <!-- 5. LATEST VIDEOS -->
    <section class="section-padding" id="videos" style="background:#080808;">
        <div class="container">
            <div class="section-header text-center reveal" style="margin-bottom:60px;">
                <span style="color:var(--secondary); font-weight:700;">VISUAL EXPERIENCE</span>
                <h2>TRENDING VIDEOS</h2>
                <p>Watch the latest official music videos in HD.</p>
            </div>

            <div class="media-scroller reveal" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php
                if ($latestVideos && mysqli_num_rows($latestVideos) > 0) {
                    while ($vid = mysqli_fetch_assoc($latestVideos)) {
                        $thumb = getMediaImage($vid['thumbnail'] ?? 'default_vid.jpg', 'video');
                ?>
                        <div class="media-card">
                            <div class="flash-badge" style="background:var(--secondary); box-shadow:0 0 10px var(--secondary);">HD</div>
                            <div class="card-img video">
                                <img src="<?= $thumb ?>" alt="Thumb">
                                <a href="user_video_view.php?id=<?= $vid['id'] ?>" class="play-overlay">
                                    <div class="play-btn" style="background:var(--secondary); box-shadow:0 0 20px var(--secondary);"><i class="fas fa-play"></i></div>
                                </a>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title"><?= htmlspecialchars($vid['title'] ?? 'Unknown Video') ?></h3>
                                <div class="card-meta">
                                    <span><?= htmlspecialchars($vid['artist'] ?? 'Artist') ?></span>
                                    <span><?= htmlspecialchars($vid['album'] ?? 'Single') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    // FALLBACK
                    for ($j = 1; $j <= 5; $j++) {
                    ?>
                        <div class="media-card">
                            <div class="flash-badge" style="background:var(--secondary);">HD</div>
                            <div class="card-img video">
                                <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=600&auto=format&fit=crop" alt="Thumb">
                                <div class="play-overlay">
                                    <div class="play-btn" style="background:var(--secondary);"><i class="fas fa-play"></i></div>
                                </div>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title">Demo Video Clip #<?= $j ?></h3>
                                <div class="card-meta"><span>Director Cut</span><span>4K</span></div>
                            </div>
                        </div>
                <?php }
                } ?>
            </div>
        </div>
    </section>

    <!-- 6. OBJECTIVES & FEATURES -->
    <section class="section-padding" id="features">
        <div class="container">
            <div class="section-header text-center reveal">
                <h2>WHY CHOOSE SOUND?</h2>
                <p>Designed to meet the objectives of modern entertainment seekers.</p>
            </div>

            <div class="features-grid reveal">
                <div class="feature-box">
                    <i class="fas fa-layer-group feature-icon"></i>
                    <h4>Structured Library</h4>
                    <p>Music and Video arranged as per Album, Artist, Year, Genre, and Language for easy navigation.</p>
                </div>
                <div class="feature-box">
                    <i class="fas fa-star feature-icon"></i>
                    <h4>Rate & Review</h4>
                    <p>Express your opinion. Users have the option of reviewing and rating all available content.</p>
                </div>
                <div class="feature-box">
                    <i class="fas fa-globe feature-icon"></i>
                    <h4>Regional & Global</h4>
                    <p>Hosting new and old Videos and Songs in both REGIONAL and ENGLISH languages.</p>
                </div>
                <div class="feature-box">
                    <i class="fas fa-search feature-icon"></i>
                    <h4>Smart Search</h4>
                    <p>Search for Music/Video based on Name, Artist, Year, or Album instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="logo">SOU<span>N</span>D</a>
                    <p>The thirst for learning, upgrading technical skills and applying the concepts in real life environment. A project implementation at your fingertips.</p>
                </div>
                <div class="footer-col">
                    <h4>EXPLORE</h4>
                    <ul>
                        <li><a href="#music">Music</a></li>
                        <li><a href="#videos">Videos</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="login.php">Login / Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>LEGAL</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Copyright</a></li>
                    </ul>
                </div>
                <div class="footer-col newsletter">
                    <h4>STAY UPDATED</h4>
                    <p style="font-size:12px; color:#666; margin-bottom:15px;">Get the latest tracks directly in your inbox.</p>
                    <form>
                        <input type="email" placeholder="Enter your email...">
                        <button type="submit" class="btn btn-primary" style="width:100%; padding:10px;">SUBSCRIBE</button>
                    </form>
                </div>
            </div>
            <div class="copyright">
                &copy; 2026 SOUND Project Group. All Rights Reserved. <br>
                <span style="opacity:0.5; font-size:10px;">Designed for Project Requirement Specification</span>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Scroll Header Logic
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.style.background = 'rgba(5, 5, 5, 0.98)';
                header.style.padding = '15px 0';
            } else {
                header.style.background = 'rgba(5, 5, 5, 0.85)';
                header.style.padding = '20px 0';
            }
        });

        // Intersection Observer for Reveal Animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));


        // Animation Text

        const texts = ["MUSIC'S & SOUND'S", "VIDIO'S & VISUALS" ];
        const animatedText = document.getElementById("animated-text");
        let textIndex = 0;
        let charIndex = 0;

        function type() {
            // Set color based on word
            if (texts[textIndex] === "REVOLUTION") {
                animatedText.style.color = "#ff0055"; // Primary color
            } else {
                animatedText.style.color = "#fff"; // Default color
            }

            if (charIndex < texts[textIndex].length) {
                animatedText.textContent += texts[textIndex].charAt(charIndex);
                charIndex++;
                setTimeout(type, 150);
            } else {
                // Wait 1 second then delete
                setTimeout(deleteText, 1000);
            }
        }

        function deleteText() {
            if (charIndex > 0) {
                animatedText.textContent = texts[textIndex].substring(0, charIndex - 1);
                charIndex--;
                setTimeout(deleteText, 100);
            } else {
                // Move to next text
                textIndex = (textIndex + 1) % texts.length;
                setTimeout(type, 500); // small delay before typing next word
            }
        }

        // Start the animation
        type();
    </script>
</body>

</html>