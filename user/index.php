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

        /* --- RESPONSIVE HEADER --- */
        header {
            background: rgba(5, 5, 5, 0.95);
            backdrop-filter: blur(20px);
            padding: 15px 5%;
            position: fixed;
            width: 100%; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border-glass);
        }

        .logo {
            font-family: 'Syncopate', sans-serif;
            font-size: 22px; color: #fff; text-decoration: none; letter-spacing: 5px;
        }
        .logo span { color: var(--primary); }

        /* Navigation Links */
        nav ul { display: flex; list-style: none; gap: 25px; align-items: center; }
        nav ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none; font-size: 11px; font-weight: 800;
            text-transform: uppercase; transition: 0.3s; letter-spacing: 1px;
        }
        nav ul li a:hover { color: var(--primary); }

        /* Mobile Menu Toggle (Three Lines) */
        .mobile-toggle {
            display: none;
            cursor: pointer;
            font-size: 22px;
            color: #fff;
        }

        /* Responsive Mobile CSS */
        @media (max-width: 991px) {
            .mobile-toggle { display: block; }

            nav {
                position: absolute;
                top: 100%; left: 0; width: 100%;
                background: rgba(5, 5, 5, 0.98);
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s ease-in-out;
                border-bottom: 1px solid var(--border-glass);
            }

            nav.active { max-height: 400px; }

            nav ul {
                flex-direction: column;
                padding: 20px 0;
                gap: 20px;
                text-align: center;
            }

            .user-actions { display: none; } /* Mobile par user icon header mein fit karne ke liye adjustments kiye ja sakte hain */
        }

        /* --- HERO SECTION --- */
        .hero {
            height: 100vh;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center;
            background: linear-gradient(rgba(5, 5, 5, 0.4), var(--bg-dark)),
                        url('https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=1920&auto=format&fit=crop');
            background-size: cover; background-position: center; background-attachment: fixed;
            padding: 0 20px;
        }

        .hero h1 { font-family: 'Syncopate'; font-size: clamp(2.5rem, 8vw, 6rem); margin-bottom: 15px; }

        /* --- CONTENT GRID RESPONSIVENESS --- */
        .section-container { padding: 80px 5%; }
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        @media (max-width: 576px) {
            .content-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
            .section-title { font-size: 16px; }
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            padding: 40px 5%;
            background: rgba(255, 255, 255, 0.02);
            text-align: center;
        }

        @media (max-width: 768px) {
            .stats-bar { grid-template-columns: repeat(2, 1fr); gap: 30px; }
            .spotlight { flex-direction: column; text-align: center; padding: 60px 5%; }
        }

        .media-card {
            background: var(--card-glass); border-radius: 15px;
            padding: 12px; border: 1px solid var(--border-glass); transition: var(--transition);
        }

        .img-box img { width: 100%; border-radius: 10px; display: block; }

        .btn-main {
            padding: 12px 30px; background: var(--primary); color: white;
            text-decoration: none; border-radius: 50px; font-weight: 800; font-size: 11px;
            letter-spacing: 2px; transition: 0.3s;
        }

        footer { padding: 40px 5%; text-align: center; border-top: 1px solid var(--border-glass); font-size: 10px; opacity: 0.5; }
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
        
        <div class="mobile-toggle" id="mobile-toggle">
            <i class="fas fa-bars"></i>
        </div>

        <nav id="nav-menu">
            <ul>
                <li><a href="index.php" style="color:var(--primary)">Home</a></li>
                <li><a href="user_music_view.php">Music</a></li>
                <li><a href="user_video_view.php">Videos</a></li>
                <li><a href="user_albums_view.php">Albums</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (!$user): ?>
                    <li class="mobile-only"><a href="login.php" style="color: var(--primary);">Login</a></li>
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
                        <a href="user_logout.php" style="color: #ff4d4d;"><i class="fas fa-power-off"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-main">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <h1 class="animate__animated animate__fadeInDown">SOUND <span>2026</span></h1>
        <p class="animate__animated animate__fadeInUp">The Visual Audio Revolution</p>
        <a href="#music" class="btn-main animate__animated animate__fadeInUp">EXPLORE NOW</a>
    </section>

    <div class="stats-bar">
        <div class="stat-item"><h2>2.4M</h2><p>Listeners</p></div>
        <div class="stat-item"><h2>45K</h2><p>Artists</p></div>
        <div class="stat-item"><h2>800+</h2><p>Uploads</p></div>
        <div class="stat-item"><h2>100%</h2><p>Lossless</p></div>
    </div>

    <section class="section-container" id="music">
        <h2 class="section-title">MUSIC NEW RELEASES</h2>
        <div class="content-grid">
            <?php while ($song = mysqli_fetch_assoc($latestMusic)):
                $musicImg = getMediaImage($song['cover_image'] ?? $song['image_path'], 'music'); ?>
                <div class="media-card">
                    <div class="img-box"><img src="<?= $musicImg ?>" alt="Music"></div>
                    <h4 style="font-size:13px; margin-top:10px;"><?= htmlspecialchars($song['title']); ?></h4>
                    <p style="font-size:11px; opacity:0.5;"><?= htmlspecialchars($song['artist']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="section-container" id="videos">
        <h2 class="section-title">FEATURED VIDEOS</h2>
        <div class="content-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
            <?php while ($video = mysqli_fetch_assoc($latestVideos)):
                $videoThumb = getMediaImage($video['thumbnail'] ?? $video['video_thumbnails'], 'video'); ?>
                <div class="media-card">
                    <div class="img-box"><img src="<?= $videoThumb ?>" alt="Video" style="aspect-ratio: 16/9; object-fit: cover;"></div>
                    <h4 style="font-size:14px; margin-top:10px;"><?= htmlspecialchars($video['title']); ?></h4>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer>
        &copy; 2026 SOUND PORTAL | DESIGNED FOR THE FUTURE
    </footer>

    <script>
        // Preloader Logic
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('loader').style.opacity = '0';
                setTimeout(() => {
                    document.getElementById('loader').style.display = 'none';
                    document.body.classList.add('visible');
                }, 500);
            }, 800);
        });

        // Mobile Menu Toggle Logic
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.getElementById('nav-menu');

        mobileToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            // Change icon from bars to times (X)
            const icon = mobileToggle.querySelector('i');
            if(navMenu.classList.contains('active')) {
                icon.classList.replace('fa-bars', 'fa-times');
            } else {
                icon.classList.replace('fa-times', 'fa-bars');
            }
        });

        // Close menu when a link is clicked
        document.querySelectorAll('nav ul li a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                mobileToggle.querySelector('i').classList.replace('fa-times', 'fa-bars');
            });
        });
    </script>
</body>

</html>