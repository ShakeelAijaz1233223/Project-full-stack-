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

// Fetch data
$latestMusic = mysqli_query($conn, "SELECT * FROM music ORDER BY id DESC LIMIT 5");
$latestVideos = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC LIMIT 4");

$user = null;
if (isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) { $user = mysqli_fetch_assoc($res); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOUND | 2026 Immersive Portal</title>
    
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
            --transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* --- SMOOTH SCROLL & SECTIONS --- */
        html { scroll-behavior: smooth; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-dark); color: #fff; overflow-x: hidden; }

        /* --- PAGE REVEAL ANIMATION --- */
        .reveal { opacity: 0; transform: translateY(50px); transition: var(--transition); }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* --- PRELOADER --- */
        #loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-dark); z-index: 9999;
            display: flex; justify-content: center; align-items: center;
        }

        /* --- MODERN HEADER --- */
        header {
            background: rgba(5, 5, 5, 0.8); backdrop-filter: blur(20px);
            padding: 15px 5%; position: fixed; width: 100%; top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border-glass);
        }

        .nav-links { display: flex; list-style: none; gap: 30px; align-items: center; }
        .dropdown { position: relative; }
        .dropdown-trigger { 
            color: rgba(255,255,255,0.7); text-decoration: none; 
            font-size: 11px; font-weight: 800; text-transform: uppercase;
            cursor: pointer; display: flex; align-items: center; gap: 5px;
        }
        .dropdown-menu {
            position: absolute; top: 40px; left: 0; background: #111;
            min-width: 200px; border-radius: 12px; padding: 10px;
            border: 1px solid var(--border-glass); opacity: 0;
            visibility: hidden; transform: translateY(10px); transition: 0.3s;
        }
        .dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .dropdown-menu a {
            display: block; color: #fff; text-decoration: none;
            padding: 10px; font-size: 12px; border-radius: 8px; transition: 0.2s;
        }
        .dropdown-menu a:hover { background: var(--primary); }

        /* --- HERO --- */
        .hero {
            height: 100vh; display: flex; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;
            background: linear-gradient(to bottom, transparent, var(--bg-dark)), 
                        url('https://images.unsplash.com/photo-1514525253361-bee8a19740c1?q=80&w=1920&auto=format&fit=crop');
            background-size: cover; background-position: center;
        }
        .hero h1 { font-family: 'Syncopate'; font-size: clamp(3rem, 10vw, 7rem); letter-spacing: -2px; }

        /* --- CONTENT BLOCKS --- */
        .section-container { padding: 120px 8%; }
        .grid-custom { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        
        .feature-card {
            background: var(--card-glass); border: 1px solid var(--border-glass);
            padding: 40px; border-radius: 30px; transition: var(--transition);
        }
        .feature-card:hover { background: rgba(255,255,255,0.07); transform: scale(1.03); }

        /* --- NEWSLETTER --- */
        .newsletter {
            background: var(--primary); padding: 100px 5%; text-align: center;
            border-radius: 50px; margin: 0 5% 100px;
        }
        .newsletter input {
            padding: 15px 30px; border-radius: 50px; border: none; width: 300px; max-width: 90%;
        }

        /* --- MOBILE RESPONSIVENESS --- */
        @media (max-width: 992px) {
            .nav-links { display: none; } /* Hide for simplicity or use a burger menu */
            .hero h1 { font-size: 3.5rem; }
        }

        .btn-main {
            padding: 15px 40px; background: var(--primary); color: #fff;
            text-decoration: none; border-radius: 50px; font-weight: 800; font-size: 12px;
            display: inline-block; transition: 0.3s;
        }
    </style>
</head>
<body>

    <div id="loader">
        <div class="animate__animated animate__pulse animate__infinite">
            <h2 style="font-family:'Syncopate'; letter-spacing:10px;">SOUND</h2>
        </div>
    </div>

    <header>
        <a href="#" class="logo" style="font-family:'Syncopate'; text-decoration:none; color:#fff; letter-spacing:3px;">SOU<span>N</span>D</a>
        
        <ul class="nav-links">
            <li class="dropdown">
                <span class="dropdown-trigger">Browse <i class="fas fa-chevron-down"></i></span>
                <div class="dropdown-menu">
                    <a href="#">New Releases</a>
                    <a href="#">Top Charts</a>
                    <a href="#">Genres</a>
                    <a href="#">Radio</a>
                </div>
            </li>
            <li class="dropdown">
                <span class="dropdown-trigger">Community <i class="fas fa-chevron-down"></i></span>
                <div class="dropdown-menu">
                    <a href="#">Artists</a>
                    <a href="#">Events</a>
                    <a href="#">Fan Clubs</a>
                    <a href="#">Podcasts</a>
                </div>
            </li>
            <li><a href="#" style="color:#fff; text-decoration:none; font-size:11px; font-weight:800;">UPGRADE</a></li>
        </ul>

        <div class="user-actions">
            <?php if($user): ?>
                <div class="dropdown">
                    <div class="dropdown-trigger" style="background:var(--card-glass); padding:8px 15px; border-radius:50px; border:1px solid var(--border-glass);">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['name']) ?>
                    </div>
                    <div class="dropdown-menu" style="left:auto; right:0;">
                        <a href="user_setting.php"><i class="fas fa-cog"></i> Profile</a>
                        <a href="user_logout.php" style="color:red;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-main" style="padding:10px 25px;">SIGN IN</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <h1 class="animate__animated animate__fadeInUp">REDEFINE SOUND</h1>
        <p class="animate__animated animate__fadeInUp" style="letter-spacing:10px; margin-top:10px; opacity:0.6;">EXPERIENCE 2026</p>
        <div style="margin-top:40px;">
            <a href="#music" class="btn-main animate__animated animate__fadeInUp">EXPLORE NOW</a>
        </div>
    </section>

    <div class="section-container reveal" style="text-align:center;">
        <div class="grid-custom">
            <div><h2 style="font-size:4rem; color:var(--primary);">90M</h2><p>Tracks Available</p></div>
            <div><h2 style="font-size:4rem; color:var(--secondary);">24/7</h2><p>Live Streaming</p></div>
            <div><h2 style="font-size:4rem; color:var(--primary);">HI-FI</h2><p>Lossless Quality</p></div>
        </div>
    </div>

    <section class="section-container reveal" id="music">
        <h2 style="font-family:'Syncopate'; margin-bottom:50px; border-left:5px solid var(--primary); padding-left:20px;">LATEST SOUNDS</h2>
        <div class="grid-custom">
            <?php while($song = mysqli_fetch_assoc($latestMusic)): ?>
                <div class="feature-card">
                    <img src="<?= getMediaImage($song['cover_image'], 'music') ?>" style="width:100%; border-radius:15px; margin-bottom:20px;">
                    <h3><?= $song['title'] ?></h3>
                    <p style="opacity:0.5;"><?= $song['artist'] ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="section-container reveal" style="background:rgba(255,255,255,0.02);">
        <h2 style="font-family:'Syncopate'; margin-bottom:50px;">CINEMATIC VISUALS</h2>
        <div class="grid-custom" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">
            <?php while($vid = mysqli_fetch_assoc($latestVideos)): ?>
                <div class="feature-card" style="padding:15px;">
                    <img src="<?= getMediaImage($vid['thumbnail'], 'video') ?>" style="width:100%; aspect-ratio:16/9; object-fit:cover; border-radius:15px;">
                    <h4 style="margin-top:15px;"><?= $vid['title'] ?></h4>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="reveal">
        <div class="newsletter">
            <h2 style="font-family:'Syncopate'; font-size:2.5rem; margin-bottom:20px;">JOIN THE REVOLUTION</h2>
            <p style="margin-bottom:30px;">Get early access to exclusive 2026 drops.</p>
            <form>
                <input type="email" placeholder="Enter your email...">
                <button type="submit" class="btn-main" style="background:#000; border:none; cursor:pointer;">SUBSCRIBE</button>
            </form>
        </div>
    </section>

    <footer style="padding:100px 5%; border-top:1px solid var(--border-glass); text-align:center;">
        <p style="opacity:0.4; letter-spacing:5px; font-size:12px;">&copy; 2026 SOUND PORTAL | PRIVACY | TERMS | CAREERS</p>
    </footer>

    <script>
        // PRELOADER
        window.addEventListener('load', () => {
            const loader = document.getElementById('loader');
            setTimeout(() => {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 500);
            }, 1000);
        });

        // SCROLL REVEAL ANIMATION SYSTEM
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    </script>
</body>
</html>