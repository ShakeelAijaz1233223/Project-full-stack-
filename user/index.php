<?php
session_start();
include_once("../config/db.php");

function getMediaImage($fileName, $type) {
    if ($type == 'music') {
        $fullPath = "../admin/uploads/music_covers/" . $fileName;
        $default = "https://placehold.co/400x400/161618/ffffff?text=Music+Cover";
    } else {
        $fullPath = "../admin/uploads/video_thumbnails/" . $fileName;
        $default = "https://placehold.co/640x360/161618/ffffff?text=Video+Thumbnail";
    }
    return (!empty($fileName) && file_exists($fullPath)) ? $fullPath : $default;
}

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
    <title>SOUND | Future of Entertainment</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #ff0055;
            --secondary: #00d4ff;
            --bg-dark: #050505;
            --card-glass: rgba(255, 255, 255, 0.03);
            --border-glass: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-dark); color: #fff; overflow-x: hidden; }

        /* --- NAVIGATION --- */
        header {
            background: rgba(5, 5, 5, 0.9);
            backdrop-filter: blur(20px);
            padding: 15px 5%;
            position: fixed;
            width: 100%;
            top: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid var(--border-glass);
        }

        .logo { font-family: 'Syncopate'; font-size: 22px; color: #fff; text-decoration: none; letter-spacing: 5px; }
        .logo span { color: var(--primary); }

        /* Desktop Nav */
        nav ul { display: flex; list-style: none; gap: 30px; }
        nav ul li a { color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; text-transform: uppercase; transition: 0.3s; opacity: 0.7; }
        nav ul li a:hover { opacity: 1; color: var(--primary); }

        /* Hamburger Menu */
        .menu-toggle { display: none; font-size: 24px; cursor: pointer; color: #fff; }

        /* --- MOBILE NAV --- */
        @media (max-width: 992px) {
            .menu-toggle { display: block; }
            nav {
                position: absolute; top: 100%; left: 0; width: 100%;
                background: #0a0a0a; flex-direction: column;
                max-height: 0; overflow: hidden; transition: 0.5s ease-in-out;
            }
            nav.active { max-height: 500px; border-bottom: 1px solid var(--primary); }
            nav ul { flex-direction: column; padding: 20px; gap: 15px; text-align: center; }
        }

        /* --- HERO --- */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.6), var(--bg-dark)), url('https://images.unsplash.com/photo-1514525253361-bee8a18744ad?q=80&w=1920&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
        }

        .hero h1 { font-family: 'Syncopate'; font-size: clamp(2.5rem, 8vw, 6rem); margin-bottom: 20px; }
        .btn-main {
            padding: 15px 40px; background: var(--primary); color: #fff;
            text-decoration: none; border-radius: 50px; font-weight: 800;
            transition: 0.3s; display: inline-block;
        }

        /* --- CONTENT SECTIONS --- */
        .section-container { padding: 100px 8%; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; }
        
        .media-card {
            background: var(--card-glass); border: 1px solid var(--border-glass);
            padding: 15px; border-radius: 20px; transition: 0.4s;
        }
        .media-card:hover { transform: translateY(-10px); border-color: var(--primary); background: rgba(255,255,255,0.06); }
        .media-card img { width: 100%; border-radius: 15px; margin-bottom: 15px; }

        /* --- FEATURE SECTION --- */
        .features { background: #080808; display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-around; }
        .feat-item { text-align: center; max-width: 300px; padding: 40px 20px; }
        .feat-item i { font-size: 40px; color: var(--primary); margin-bottom: 20px; }

        /* --- NEWSLETTER --- */
        .newsletter {
            background: linear-gradient(45deg, #121212, #1a1a1a);
            text-align: center; padding: 80px 5%; border-radius: 30px; margin: 50px 5%;
        }
        .newsletter input {
            padding: 15px 25px; width: 300px; border-radius: 30px; border: none; outline: none; background: #222; color: #fff;
        }

        footer { padding: 50px; text-align: center; border-top: 1px solid var(--border-glass); opacity: 0.6; font-size: 12px; }
    </style>
</head>
<body>

    <header>
        <a href="#" class="logo">SOU<span>N</span>D</a>
        
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <nav id="nav-list">
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#music">Music</a></li>
                <li><a href="#videos">Videos</a></li>
                <li><a href="#pricing">Premium</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>

        <div class="user-actions">
            <?php if($user): ?>
                <span style="font-size:12px; font-weight:bold; color:var(--primary)">Hi, <?= $user['name'] ?></span>
            <?php else: ?>
                <a href="login.php" class="btn-main" style="padding: 8px 20px; font-size: 11px;">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero" id="home">
        <h1 data-aos="zoom-out" data-aos-duration="1500">EXPERIENCE <br><span>THE BASS</span></h1>
        <p data-aos="fade-up" style="letter-spacing: 5px; margin-bottom: 30px;">THE 2026 AUDIO REVOLUTION IS HERE</p>
        <a href="#music" class="btn-main" data-aos="fade-up" data-aos-delay="400">START LISTENING</a>
    </section>

    <section class="features section-container">
        <div class="feat-item" data-aos="fade-up">
            <i class="fas fa-bolt"></i>
            <h3>Ultra Fast</h3>
            <p>Zero-latency streaming with our new 2026 edge servers.</p>
        </div>
        <div class="feat-item" data-aos="fade-up" data-aos-delay="200">
            <i class="fas fa-cloud-download-alt"></i>
            <h3>Offline Mode</h3>
            <p>Save your favorite tracks and listen anywhere, anytime.</p>
        </div>
        <div class="feat-item" data-aos="fade-up" data-aos-delay="400">
            <i class="fas fa-headset"></i>
            <h3>Spatial Audio</h3>
            <p>360-degree immersive sound for a concert-like experience.</p>
        </div>
    </section>

    <section class="section-container" id="music">
        <h2 style="font-family: 'Syncopate'; margin-bottom: 40px;" data-aos="fade-right">NEW RELEASES</h2>
        <div class="grid">
            <?php while($song = mysqli_fetch_assoc($latestMusic)): 
                $img = getMediaImage($song['cover_image'], 'music'); ?>
                <div class="media-card" data-aos="fade-up">
                    <img src="<?= $img ?>" alt="Song">
                    <h4><?= $song['title'] ?></h4>
                    <p style="opacity:0.5; font-size: 13px;"><?= $song['artist'] ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <div class="newsletter" data-aos="flip-up">
        <h2>JOIN THE COMMUNITY</h2>
        <p style="margin: 15px 0 30px; opacity: 0.7;">Get early access to exclusive drops and artist merch.</p>
        <form>
            <input type="email" placeholder="Enter your email...">
            <button class="btn-main" style="border:none; cursor:pointer; margin-left: 10px;">JOIN</button>
        </form>
    </div>

    <section class="section-container" id="videos">
        <h2 style="font-family: 'Syncopate'; margin-bottom: 40px;" data-aos="fade-right">FEATURED VIDEOS</h2>
        <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));">
            <?php while($vid = mysqli_fetch_assoc($latestVideos)): 
                $thumb = getMediaImage($vid['thumbnail'], 'video'); ?>
                <div class="media-card" data-aos="zoom-in">
                    <img src="<?= $thumb ?>" style="aspect-ratio: 16/9; object-fit:cover;">
                    <h4><?= $vid['title'] ?></h4>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <footer>
        <p>&copy; 2026 SOUND PORTAL | ALL RIGHTS RESERVED | <a href="#" style="color:var(--primary)">PRIVACY POLICY</a></p>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS Animation
        AOS.init({
            duration: 1000,
            once: false,
            mirror: true
        });

        // Mobile Menu Toggle
        const menuToggle = document.getElementById('mobile-menu');
        const navList = document.getElementById('nav-list');

        menuToggle.addEventListener('click', () => {
            navList.classList.toggle('active');
            // Change icon
            const icon = menuToggle.querySelector('i');
            if(navList.classList.contains('active')) {
                icon.classList.replace('fa-bars', 'fa-times');
            } else {
                icon.classList.replace('fa-times', 'fa-bars');
            }
        });

        // Smooth Scroll Close Menu
        document.querySelectorAll('nav ul li a').forEach(link => {
            link.addEventListener('click', () => {
                navList.classList.remove('active');
            });
        });
    </script>
</body>
</html>