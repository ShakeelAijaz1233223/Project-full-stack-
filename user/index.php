<?php
session_start();
include_once("../config/db.php");

/**
 * PATH FIXER HELPER
 */
function getMediaImage($fileName, $type)
{
    if ($type == 'music') {
        $fullPath = "../admin/uploads/music_covers/" . $fileName;
        $default = "https://placehold.co/400x400/161618/ffffff?text=Music+Cover";
    } else {
        $fullPath = "../admin/uploads/video_thumbnails/" . $fileName;
        $default = "https://placehold.co/640x360/161618/ffffff?text=Video+Thumbnail";
    }
    return (!empty($fileName) && file_exists($fullPath)) ? $fullPath : $default;
}

// Fetch latest music and videos
$latestMusic = mysqli_query($conn, "SELECT * FROM music ORDER BY id DESC LIMIT 5");
$latestVideos = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC LIMIT 4");

// Login check
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
    <title>SOUND | 2026 Immersive Experience</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #ff0055;
            --secondary: #00d4ff;
            --bg-dark: #050505;
            --card-glass: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.1);
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* --- PRELOADER --- */
        #loader {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: var(--bg-dark);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease;
        }

        .bar-container {
            display: flex;
            align-items: flex-end;
            height: 30px;
            gap: 4px;
        }

        .bar {
            width: 4px;
            background: var(--primary);
            animation: bounce 0.5s ease-in-out infinite alternate;
        }

        .bar:nth-child(2) { animation-delay: 0.1s; }
        .bar:nth-child(3) { animation-delay: 0.2s; }
        .bar:nth-child(4) { animation-delay: 0.3s; }

        @keyframes bounce {
            from { height: 5px; }
            to { height: 30px; }
        }

        /* --- BASE STYLES --- */
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: #fff;
            overflow-x: hidden;
            opacity: 0;
            transition: opacity 1.2s ease-in-out;
        }

        body.visible { opacity: 1; }

        /* --- HEADER & NAV --- */
        header {
            background: rgba(5, 5, 5, 0.85);
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
            font-family: 'Syncopate', sans-serif;
            font-size: 20px;
            color: #fff;
            text-decoration: none;
            letter-spacing: 4px;
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
        }

        nav ul li a:hover { color: var(--primary); }

        /* --- HERO --- */
        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(rgba(5, 5, 5, 0.4), var(--bg-dark)),
                        url('https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            padding: 0 20px;
        }

        .hero h1 {
            font-family: 'Syncopate';
            font-size: clamp(2.5rem, 8vw, 5rem);
            margin-bottom: 10px;
        }

        .hero p {
            font-size: clamp(10px, 2vw, 14px);
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        /* --- CONTENT GRID & CARDS --- */
        .section-container { padding: 80px 5%; }

        .section-title {
            font-family: 'Syncopate';
            font-size: 18px;
            margin-bottom: 40px;
            border-left: 4px solid var(--primary);
            padding-left: 15px;
            letter-spacing: 2px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
        }

        .media-card {
            background: var(--card-glass);
            border-radius: 15px;
            padding: 12px;
            border: 1px solid var(--border-glass);
            transition: var(--transition);
        }

        .media-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.06);
        }

        .img-box img {
            width: 100%;
            border-radius: 10px;
            transition: 0.5s;
        }

        /* --- PRICING --- */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .price-card {
            background: var(--card-glass);
            padding: 40px;
            border-radius: 25px;
            text-align: center;
            border: 1px solid var(--border-glass);
        }

        .price-card.featured {
            border-color: var(--primary);
            background: rgba(255, 0, 85, 0.03);
            transform: scale(1.05);
        }

        /* --- UTILS --- */
        .btn-main {
            padding: 14px 30px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 800;
            font-size: 11px;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-main:hover {
            box-shadow: 0 0 20px rgba(255, 0, 85, 0.4);
            transform: translateY(-2px);
        }

        /* --- RESPONSIVE MOBILE FIXES --- */
        @media (max-width: 992px) {
            nav { display: none; } /* Mobile menu can be added as a burger */
            .stats-bar { grid-template-columns: repeat(2, 1fr); gap: 30px; }
        }

        @media (max-width: 480px) {
            .section-container { padding: 60px 5%; }
            .content-grid { grid-template-columns: 1fr; }
            .price-card.featured { transform: scale(1); }
        }
    </style>
</head>

<body>

    <div id="loader">
        <div class="bar-container">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
    </div>

    <header>
        <a href="index.php" class="logo">SOU<span>N</span>D</a>
        <nav>
            <ul>
                <li><a href="index.php" style="color:var(--primary)">Home</a></li>
                <li><a href="user_music_view.php">Music</a></li>
                <li><a href="user_video_view.php">Videos</a></li>
                <li><a href="user_albums_view.php">Albums</a></li>
                <li><a href="about.php">About</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <?php if ($user): ?>
                <div class="user-dropdown">
                    <div class="user-trigger">
                        <div style="width: 28px; height: 28px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
                            <?= strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-main" style="padding: 8px 20px;">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <h1 class="animate__animated animate__fadeInDown">SOUND <span>2026</span></h1>
        <p class="animate__animated animate__fadeInUp animate__delay-1s">The Visual Audio Revolution</p>
        <a href="#music" class="btn-main animate__animated animate__zoomIn animate__delay-2s">EXPLORE NOW</a>
    </section>

    <div class="stats-bar" style="display: grid; grid-template-columns: repeat(4, 1fr); padding: 50px 5%; text-align: center; background: rgba(255,255,255,0.01); border-bottom: 1px solid var(--border-glass);">
        <div class="stat-item"><h2>2.4M</h2><p style="font-size:10px; opacity:0.5;">LISTENERS</p></div>
        <div class="stat-item"><h2>45K</h2><p style="font-size:10px; opacity:0.5;">ARTISTS</p></div>
        <div class="stat-item"><h2>800+</h2><p style="font-size:10px; opacity:0.5;">UPLOADS</p></div>
        <div class="stat-item"><h2>100%</h2><p style="font-size:10px; opacity:0.5;">LOSSLESS</p></div>
    </div>

    <section class="section-container" id="music">
        <h2 class="section-title">MUSIC RELEASES</h2>
        <div class="content-grid">
            <?php while ($song = mysqli_fetch_assoc($latestMusic)): 
                $musicImg = getMediaImage($song['cover_image'] ?? $song['image_path'] ?? '', 'music');
            ?>
                <div class="media-card animate__animated animate__fadeInUp">
                    <div class="img-box">
                        <img src="<?= $musicImg ?>" alt="Cover" style="aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                    <h4 style="font-size:14px; margin-top:10px;"><?= htmlspecialchars($song['title']); ?></h4>
                    <p style="font-size:11px; opacity:0.5;"><?= htmlspecialchars($song['artist']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="section-container" id="pricing" style="background: rgba(255,255,255,0.01);">
        <h2 class="section-title">MEMBERSHIP</h2>
        <div class="pricing-grid">
            <div class="price-card">
                <h3>FREE</h3>
                <div style="font-size: 35px; font-weight: 800; margin: 15px 0;">$0</div>
                <p style="font-size: 13px; opacity: 0.6; margin-bottom: 25px;">Standard Audio Quality<br>With Advertisements</p>
                <a href="#" class="btn-main" style="background:transparent; border:1px solid #fff;">START FREE</a>
            </div>
            <div class="price-card featured">
                <span style="font-size: 9px; background: var(--primary); padding: 3px 10px; border-radius: 4px;">RECOMMENDED</span>
                <h3>PREMIUM</h3>
                <div style="font-size: 35px; font-weight: 800; margin: 15px 0;">$9</div>
                <p style="font-size: 13px; opacity: 0.8; margin-bottom: 25px;">Hi-Fi Lossless Audio<br>Offline & Ad-Free</p>
                <a href="#" class="btn-main">UPGRADE NOW</a>
            </div>
        </div>
    </section>

    <footer style="padding: 60px 5%; text-align: center; border-top: 1px solid var(--border-glass); opacity: 0.6; font-size: 10px; letter-spacing: 2px;">
        &copy; 2026 SOUND PORTAL | ALL RIGHTS RESERVED
    </footer>

    <script>
        // Smooth Loader Exit & Entry Animation
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                    document.body.classList.add('visible');
                }, 500);
            }, 1000);
        });

        // Simple Smooth Scroll for Anchors
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>