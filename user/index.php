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
            top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-dark);
            z-index: 9999;
            display: flex; justify-content: center; align-items: center;
        }

        .bar-container { display: flex; align-items: flex-end; height: 30px; gap: 4px; }
        .bar { width: 4px; background: var(--primary); animation: bounce 0.5s ease-in-out infinite alternate; }
        .bar:nth-child(2) { animation-delay: 0.1s; }
        .bar:nth-child(3) { animation-delay: 0.2s; }
        @keyframes bounce { from { height: 5px; } to { height: 30px; } }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }

        body { background-color: var(--bg-dark); color: #fff; overflow-x: hidden; opacity: 0; transition: opacity 1s; }
        body.visible { opacity: 1; }

        /* --- HEADER --- */
        header {
            background: rgba(5, 5, 5, 0.9);
            backdrop-filter: blur(20px);
            padding: 18px 5%;
            position: fixed;
            width: 100%; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border-glass);
        }

        .logo {
            font-family: 'Syncopate', sans-serif;
            font-size: clamp(16px, 4vw, 22px);
            color: #fff; text-decoration: none; letter-spacing: 5px;
        }
        .logo span { color: var(--primary); }

        nav ul { display: flex; list-style: none; gap: 20px; }
        nav ul li a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none; font-size: 11px; font-weight: 800;
            text-transform: uppercase; transition: 0.3s; letter-spacing: 1px;
        }
        nav ul li a:hover { color: var(--primary); }

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
            padding: 8px 16px; border-radius: 50px; cursor: pointer;
            border: 1px solid var(--border-glass); display: flex; align-items: center; gap: 10px;
            transition: var(--transition);
        }
        .user-dropdown { position: relative; }
        .dropdown-content {
            position: absolute; right: 0; top: 55px;
            background: rgba(15, 15, 17, 0.98); backdrop-filter: blur(25px);
            min-width: 200px; border-radius: 18px; padding: 10px;
            border: 1px solid var(--border-glass); opacity: 0; visibility: hidden;
            transform: translateY(10px); transition: var(--transition); z-index: 1001;
        }
        .user-dropdown:hover .dropdown-content { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-content a {
            color: #fff; padding: 10px 15px; text-decoration: none; display: flex;
            align-items: center; gap: 12px; font-size: 12px; font-weight: 600;
            border-radius: 10px; transition: 0.3s;
        }
        .dropdown-content a i { color: var(--primary); width: 15px; }
        .dropdown-content a:hover { background: rgba(255, 0, 85, 0.1); transform: translateX(5px); }

        /* --- HERO --- */
        .hero {
            height: 100vh; display: flex; flex-direction: column; justify-content: center;
            align-items: center; text-align: center;
            background: linear-gradient(rgba(5, 5, 5, 0.2), var(--bg-dark)),
                url('https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=1920&auto=format&fit=crop');
            background-size: cover; background-position: center; background-attachment: fixed;
        }
        .hero h1 { font-family: 'Syncopate'; font-size: clamp(3rem, 10vw, 6rem); margin-bottom: 15px; line-height: 1; }
        .hero p { font-size: 16px; text-transform: uppercase; letter-spacing: 8px; opacity: 0.8; margin-bottom: 30px; }

        /* --- SECTIONS --- */
        .stats-bar {
            display: grid; grid-template-columns: repeat(4, 1fr);
            padding: 60px 5%; background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-glass); text-align: center;
        }
        .stat-item h2 { font-family: 'Syncopate'; color: var(--primary); font-size: 24px; }
        .stat-item p { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.5; }

        .section-container { padding: 100px 5%; }
        .section-title {
            font-family: 'Syncopate'; font-size: 20px; margin-bottom: 50px;
            border-left: 5px solid var(--primary); padding-left: 20px; letter-spacing: 3px;
        }
        .content-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 30px; }
        .media-card {
            background: var(--card-glass); border-radius: 20px; padding: 15px;
            border: 1px solid var(--border-glass); transition: var(--transition);
        }
        .media-card:hover { border-color: var(--primary); transform: translateY(-10px); background: rgba(255, 255, 255, 0.08); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4); }
        .img-box { position: relative; border-radius: 15px; overflow: hidden; margin-bottom: 15px; }
        .img-box img { width: 100%; display: block; transition: 0.6s; }

        .btn-main {
            display: inline-block; padding: 15px 40px; background: var(--primary);
            color: white; text-decoration: none; border-radius: 50px;
            font-weight: 800; font-size: 12px; letter-spacing: 2px; transition: 0.3s;
        }
        .btn-main:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(255, 0, 85, 0.3); }

        .spotlight {
            background: linear-gradient(90deg, #050505 30%, transparent),
                url('https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=1920&auto=format&fit=crop');
            background-size: cover; padding: 120px 5%; display: flex; align-items: center; min-height: 600px;
        }
        .spotlight-content { max-width: 600px; }
        .spotlight-content h2 { font-family: 'Syncopate'; font-size: 40px; margin-bottom: 20px; }
        .tag { background: var(--primary); padding: 5px 15px; border-radius: 5px; font-size: 10px; font-weight: 800; }

        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .price-card {
            background: var(--card-glass); padding: 50px 40px; border-radius: 30px;
            border: 1px solid var(--border-glass); text-align: center; transition: 0.3s;
        }
        .price-card.featured { border-color: var(--primary); background: rgba(255, 0, 85, 0.05); }
        .price-card h3 { font-family: 'Syncopate'; font-size: 18px; margin-bottom: 20px; }
        .price-card .cost { font-size: 48px; font-weight: 800; margin-bottom: 20px; }
        .price-card .cost span { font-size: 16px; opacity: 0.5; }

        footer { padding: 80px 5% 40px; text-align: center; border-top: 1px solid var(--border-glass); font-size: 11px; opacity: 0.5; letter-spacing: 2px; }

        /* --- RESPONSIVE FIXES --- */
        @media (max-width: 992px) {
            .menu-btn { display: block; }
            nav {
                position: absolute; top: 100%; left: 0; width: 100%;
                background: rgba(5, 5, 5, 0.98); backdrop-filter: blur(20px);
                border-bottom: 1px solid var(--border-glass);
                display: none; padding: 20px 0;
            }
            nav.active { display: block; }
            nav ul { flex-direction: column; align-items: center; gap: 15px; }
            .user-actions { display: none; } /* Mobile par cleaner look ke liye hide kiya hai */
        }

        @media (max-width: 768px) {
            .stats-bar { grid-template-columns: repeat(2, 1fr); gap: 20px; }
            .hero p { letter-spacing: 4px; font-size: 14px; }
            .section-container { padding: 60px 5%; }
            .spotlight { background: var(--bg-dark); text-align: center; justify-content: center; }
        }
    </style>
</head>

<body>

    <div id="loader">
        <div class="bar-container">
            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
        </div>
    </div>

    <header>
        <a href="index.php" class="logo">SOU<span>N</span>D</a>
        
        <div class="menu-btn" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>

        <nav id="nav-bar">
            <ul>
                <li><a href="index.php" style="color:var(--primary)">Home</a></li>
                <li><a href="user_music_view.php">Music</a></li>
                <li><a href="user_video_view.php">Videos</a></li>
                <li><a href="user_albums_view.php">Albums</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (!$user): ?>
                    <li class="mobile-only"><a href="login.php" style="color:var(--primary)">Login</a></li>
                <?php endif; ?>
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

    <section class="hero" id="home">
        <h1 class="animate__animated animate__fadeInDown">SOUND <span>2026</span></h1>
        <p class="animate__animated animate__fadeInUp">The Visual Audio Revolution</p>
        <a href="#music" class="btn-main animate__animated animate__fadeInUp">EXPLORE NOW</a>
    </section>

    <div class="stats-bar">
        <div class="stat-item"><h2>2.4M</h2><p>Active Listeners</p></div>
        <div class="stat-item"><h2>45K</h2><p>Artists</p></div>
        <div class="stat-item"><h2>800+</h2><p>Daily Uploads</p></div>
        <div class="stat-item"><h2>100%</h2><p>Lossless Audio</p></div>
    </div>

    <section class="section-container" id="music">
        <h2 class="section-title">MUSIC NEW RELEASES</h2>
        <div class="content-grid">
            <?php while ($song = mysqli_fetch_assoc($latestMusic)):
                $musicImg = getMediaImage($song['cover_image'] ?? $song['image_path'], 'music');
            ?>
                <div class="media-card">
                    <div class="img-box">
                        <img src="<?= $musicImg ?>" alt="Music" style="aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                    <h4 style="font-size:14px;"><?= htmlspecialchars($song['title']); ?></h4>
                    <p style="font-size:12px; opacity:0.5;"><?= htmlspecialchars($song['artist']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="spotlight">
        <div class="spotlight-content">
            <span class="tag">ARTIST OF THE MONTH</span>
            <h2 style="margin-top:20px;">XENON ECHO</h2>
            <p style="opacity:0.7; line-height:1.8; margin-bottom:30px;">Pushing the boundaries of electronic synthesis. Experience the new album 'Neon Horizons' in Spatial Audio exclusively on SOUND.</p>
            <a href="#" class="btn-main">LISTEN TO ALBUM</a>
        </div>
    </section>

    <section class="section-container" id="videos" style="background: rgba(255,255,255,0.01);">
        <h2 class="section-title">FEATURED NEW VIDEOS</h2>
        <div class="content-grid" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));">
            <?php while ($video = mysqli_fetch_assoc($latestVideos)):
                $videoThumb = getMediaImage($video['thumbnail'] ?? $video['video_thumbnails'], 'video');
            ?>
                <div class="media-card">
                    <div class="img-box">
                        <img src="<?= $videoThumb ?>" alt="Video" style="aspect-ratio: 16/9; object-fit: cover;">
                    </div>
                    <h4 style="font-size:15px;"><?= htmlspecialchars($video['title']); ?></h4>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="section-container" id="pricing">
        <h2 class="section-title">CHOOSE YOUR VIBE</h2>
        <div class="pricing-grid">
            <div class="price-card">
                <h3>FREE</h3>
                <div class="cost">$0<span>/mo</span></div>
                <ul style="list-style:none; opacity:0.6; font-size:13px; margin-bottom:30px; line-height:2;">
                    <li>Standard Audio Quality</li>
                    <li>With Advertisements</li>
                    <li>Mobile & Desktop Access</li>
                </ul>
                <a href="#" class="btn-main" style="background:transparent; border:1px solid white;">GET STARTED</a>
            </div>
            <div class="price-card featured">
                <h3>PREMIUM</h3>
                <div class="cost">$9<span>/mo</span></div>
                <ul style="list-style:none; opacity:0.8; font-size:13px; margin-bottom:30px; line-height:2;">
                    <li>Lossless Audio (Hi-Fi)</li>
                    <li>Zero Advertisements</li>
                    <li>Offline Downloads</li>
                    <li>Early Access to Videos</li>
                </ul>
                <a href="#" class="btn-main">GO PREMIUM</a>
            </div>
        </div>
    </section>

    <footer>
        <div style="margin-bottom: 30px;">
            <a href="#" style="color:white; margin:0 15px; text-decoration:none;">PRIVACY</a>
            <a href="#" style="color:white; margin:0 15px; text-decoration:none;">TERMS</a>
            <a href="#" style="color:white; margin:0 15px; text-decoration:none;">CAREERS</a>
        </div>
        &copy; 2026 SOUND PORTAL | DESIGNED FOR THE FUTURE
    </footer>

    <script>
        // Preloader
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loader').style.opacity = '0';
                setTimeout(() => {
                    document.getElementById('loader').style.display = 'none';
                    document.body.classList.add('visible');
                }, 500);
            }, 800);
        });

        // Mobile Menu Toggle
        function toggleMenu() {
            const nav = document.getElementById('nav-bar');
            nav.classList.toggle('active');
            const icon = document.querySelector('.menu-btn i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        }
    </script>
</body>

</html>