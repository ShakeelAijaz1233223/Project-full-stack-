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
        /* --- ROOT VARIABLES --- */
:root {
    --primary: #ff0055;
    --primary-hover: #d90049;
    --secondary: #00d4ff;
    --bg-dark: #050505;
    --bg-card: #0f0f0f;
    --text-main: #ffffff;
    --text-muted: #888888;
    --glass: rgba(255, 255, 255, 0.05);
    --border-glass: rgba(255, 255, 255, 0.1);
    --font-head: 'Syncopate', sans-serif;
    --font-body: 'Plus Jakarta Sans', sans-serif;
    --transition: 0.3s ease;
}

/* --- GLOBAL RESET --- */
* { margin:0; padding:0; box-sizing:border-box; scroll-behavior: smooth; }
body { background: var(--bg-dark); color: var(--text-main); font-family: var(--font-body); overflow-x:hidden; }
a { text-decoration: none; color: inherit; transition: 0.3s; }
ul { list-style:none; }

/* --- CONTAINER & UTILS --- */
.container { max-width: 1400px; margin:0 auto; padding:0 5%; }
.text-center { text-align:center; }
.section-padding { padding:100px 0; }

/* --- HEADER --- */
header {
    position: fixed;
    top:0; left:0; width:100%;
    background: rgba(5,5,5,0.85);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border-glass);
    padding: 20px 0;
    z-index: 1000;
}
.nav-wrapper { display:flex; justify-content:space-between; align-items:center; }
.logo { font-family: var(--font-head); font-size:24px; font-weight:700; letter-spacing:2px; color: var(--text-main);}
.logo span { color: var(--primary); }

/* --- NAV LINKS --- */
.nav-links { display:flex; gap:30px; }
.nav-links a {
    font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px;
    color: rgba(255,255,255,0.7);
}
.nav-links a:hover, .nav-links a.active { color: var(--primary); }

/* --- USER DROPDOWN --- */
.user-actions { display:flex; align-items:center; gap:10px; }
.user-dropdown { position:relative; }
.user-trigger {
    background: rgba(255,255,255,0.05);
    padding:8px 16px; border-radius:50px; border:1px solid var(--border-glass);
    display:flex; align-items:center; gap:10px; cursor:pointer; transition: var(--transition);
}
.user-initial { width:25px; height:25px; background: var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800; }
.user-trigger span { font-size:12px; font-weight:700; }
.user-trigger i { font-size:9px; opacity:0.5; }

.dropdown-content {
    position:absolute; right:0; top:55px;
    background: rgba(15,15,17,0.98);
    backdrop-filter: blur(25px);
    min-width:200px; border-radius:18px; padding:10px;
    border:1px solid var(--border-glass);
    opacity:0; visibility:hidden; transform: translateY(10px);
    transition: var(--transition);
    display:flex; flex-direction:column;
    z-index:1001;
}
.user-dropdown:hover .dropdown-content { opacity:1; visibility:visible; transform:translateY(0); }
.dropdown-content a { color:#fff; padding:10px 15px; display:flex; align-items:center; gap:12px; font-size:12px; font-weight:600; border-radius:10px; transition:0.3s; }
.dropdown-content a i { color: var(--primary); width:15px; }
.dropdown-content a:hover { background: rgba(255,0,85,0.1); transform: translateX(5px);}
.logout { color: #ff4d4d; }
.login-btn {
    background: var(--primary); padding:8px 22px; border-radius:30px; font-size:11px; font-weight:800; color:#fff; transition:0.3s;
}
.login-btn:hover { opacity:0.8; }

/* --- HAMBURGER --- */
.hamburger { display:none; flex-direction:column; gap:4px; cursor:pointer; }
.hamburger span { width:25px; height:3px; background:white; border-radius:2px; transition:0.3s; }
.hamburger.active span:nth-child(1) { transform:rotate(45deg) translate(5px,5px); }
.hamburger.active span:nth-child(2) { opacity:0; }
.hamburger.active span:nth-child(3) { transform:rotate(-45deg) translate(5px,-5px); }

/* --- RESPONSIVE --- */
@media (max-width:992px) {
    .nav-links { 
        position:fixed; top:70px; right:-100%; 
        background: var(--bg-dark); 
        height: calc(100vh - 70px); width:200px; 
        flex-direction:column; padding-top:20px; gap:0; 
        transition:0.3s; 
        z-index:999;
    }
    .nav-links.show { right:0; }
    .nav-links a { padding:12px 20px; }
    .hamburger { display:flex; }
}

/* --- REST OF YOUR EXISTING CSS --- */
/* Add here everything from your existing code like hero, about, features, media, stats, footer, etc., exactly as you had */
/* For brevity, I'm not repeating all sections, but in your final CSS you paste everything below header code */

    </style>
</head>

<body>

   <!-- Header -->
<header id="header">
    <div class="container nav-wrapper">
        <a href="index.php" class="logo">SOU<span>N</span>D</a>

        <!-- Hamburger for mobile -->
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="nav-links" id="nav-links">
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
                        <div class="user-initial">
                            <?= strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span><?= htmlspecialchars($user['name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content">
                        <a href="user_setting.php"><i class="fas fa-cog"></i> Settings</a>
                        <div class="divider"></div>
                        <a href="user_logout.php" class="logout"><i class="fas fa-power-off"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">LOGIN</a>
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

        const texts = ["MUSIC'S & SOUND'S", "VIDIO'S & ALBUMS" ];
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