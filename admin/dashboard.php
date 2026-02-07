<?php
session_start();
include "../config/db.php";

// Image helper - ensures we don't get broken images
function getImagePath($imageName, $subfolder = '')
{
    $path = 'uploads/' . ($subfolder ? $subfolder . '/' : '') . $imageName;
    if (!empty($imageName) && file_exists($path)) {
        return $path;
    }
    return 'uploads/default.png';
}

// 1. Login check
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// 2. Fetch stats
$musicCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM music"))['total'] ?? 0;
$videoCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM videos"))['total'] ?? 0;
$albumCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM albums"))['total'] ?? 0;
$userCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;

// 3. Fetch Latest Uploads
$musicList = mysqli_query($conn, "SELECT cover_image FROM music ORDER BY id DESC LIMIT 12");
$videoList = mysqli_query($conn, "SELECT thumbnail FROM videos ORDER BY id DESC LIMIT 12");
$albumList = mysqli_query($conn, "SELECT cover FROM albums ORDER BY id DESC LIMIT 12");

// 4. Fetch Logged-in Admin Info
$userEmail = $_SESSION['email'];
$userQuery = mysqli_query($conn, "SELECT * FROM admin_users WHERE email='$userEmail'");
$userData  = mysqli_fetch_assoc($userQuery);

$profileImg = getImagePath($userData['avatar'] ?? 'default.png');
$userName = !empty($userData['name']) ? $userData['name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Sound Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #0f111a;
            --accent-color: #e14eca;
            --secondary-accent: #357ffa;
            --body-bg: #f4f7fe;
            --glass-white: rgba(255, 255, 255, 0.9);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--body-bg);
            color: #2d3748;
            overflow-x: hidden;
        }

        /* Premium Loader */
        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 99999;
        }

        .loader-ring {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Sidebar Responsive */
        .sidebar {
            position: fixed;
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            padding: 30px 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            left: 0;
        }

        /* Sidebar Close Button Style (Mobile Only) */
        .btn-sidebar-close {
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            transition: 0.3s;
            margin-bottom: 10px;
        }

        .btn-sidebar-close:hover {
            color: var(--accent-color);
            transform: rotate(90deg);
        }

        .sidebar h2 {
            color: #fff;
            font-weight: 700;
            font-size: 22px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .sidebar h2 i {
            color: var(--accent-color);
            margin-right: 12px;
        }

        .sidebar a {
            padding: 12px 18px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: linear-gradient(45deg, var(--accent-color), var(--secondary-accent));
            color: #fff;
            box-shadow: 0 8px 15px rgba(225, 78, 202, 0.2);
        }

        .sidebar a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        /* Top Navbar */
        .top-navbar {
            position: fixed;
            left: 280px;
            right: 0;
            height: 70px;
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            z-index: 1000;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s;
        }

        .main-content {
            margin-left: 280px;
            padding: 100px 30px 40px;
            transition: all 0.4s;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }

        /* Modern Cards */
        .card-stats {
            background: #fff;
            border: none;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07);
        }

        .card-stats h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0 0;
        }

        .card-stats p {
            margin: 0;
            font-size: 13px;
            color: #8898aa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Swiper Fixes */
        .swiper-slide {
            border-radius: 18px;
            overflow: hidden;
            height: 240px;
            background: #000;
            border: 3px solid #fff;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Tablet & Mobile Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                left: -280px;
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar.active+.sidebar-overlay {
                display: block;
            }

            .main-content {
                margin-left: 0;
                padding: 90px 20px 40px;
            }

            .top-navbar {
                left: 0;
                padding: 0 20px;
            }

            .mobile-toggle {
                display: block;
                cursor: pointer;
                font-size: 1.5rem;
                margin-right: 15px;
            }
        }

        @media (max-width: 576px) {
            .card-stats {
                padding: 20px;
            }

            .swiper-slide {
                height: 180px;
            }
        }

        .dropdown-menu.show {
            animation: fadeInDown 0.3s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div id="pageLoader">
        <div class="loader-ring"></div>
        <p class="mt-3 fw-bold text-muted text-uppercase" style="letter-spacing: 2px; font-size: 12px;">Harmonix Loading</p>
    </div>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <div class="d-flex d-lg-none justify-content-end mb-2">
            <i class="fa-solid fa-xmark btn-sidebar-close" onclick="toggleSidebar()"></i>
        </div>

        <h2><i class="fa-solid fa-compact-disc fa-spin"></i> Sound Music</h2>
        <nav>
            <a href="dashboard.php" class="active"><i class="fa-solid fa-grip"></i> Dashboard</a>
            <a href="profile.php"><i class="fa-regular fa-user-circle"></i> Profile</a>
            <a href="user.php"><i class="fa-solid fa-users-viewfinder"></i> Users</a>
            <a href="Music_View.php"><i class="fa-solid fa-music"></i> Musics</a>
            <a href="Video_View.php"><i class="fa-solid fa-play-circle"></i> Videos</a>
            <a href="albums_View.php"><i class="fa-solid fa-record-vinyl"></i> Albums</a>
            <a href="settings.php"><i class="fa-solid fa-sliders"></i> Settings</a>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 15px 0;">
            <a href="add_albums.php"><i class="fa-solid fa-folder-plus"></i> Add Album</a>
            <a href="add_music.php"><i class="fa-solid fa-cloud-arrow-up"></i> Add Music</a>
            <a href="add_video.php"><i class="fa-solid fa-file-video"></i> Add Video</a>
            <a href="music_reviews.php"><i class="fa-solid fa-compact-disc></i> Music_Reviews</a>
            <a href="video_review.php"><i class="fa-solid fa-envelope-open-text"></i> Video_Reviews</a>
            <a href="albums_review.php"><i class="fa-solid fa-envelope-open-text"></i> Album_Reviews</a>
            <a href="admin_messages.php"><i class="fa-solid fa-envelope-open-text"></i> Messages</a>
           
            <a href="logout.php" class="text-danger mt-3"><i class="fa-solid fa-power-off"></i> Sign Out</a>
        </nav>
    </div>

    <div class="top-navbar">
        <div class="d-flex align-items-center">
            <div class="mobile-toggle d-lg-none" onclick="toggleSidebar()"><i class="fa-solid fa-bars-staggered"></i></div>
            <h5 class="mb-0 fw-bold d-none d-sm-block">System Overview</h5>
        </div>

        <div class="dropdown">
            <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="mb-0 fw-bold small"><?php echo htmlspecialchars($userName); ?></p>
                    <span class="badge rounded-pill" style="font-size: 9px; background: rgba(53,127,250,0.1); color: var(--secondary-accent);">Verified Admin</span>
                </div>
                <img src="<?php echo $profileImg; ?>" class="rounded-circle border border-2 border-white shadow-sm" width="45" height="45" style="object-fit: cover;">
            </div>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 mt-2" style="border-radius: 15px; min-width: 220px;">
                <li class="d-flex justify-content-between align-items-center px-3 py-2 mb-1 border-bottom d-md-none">
                    <span class="fw-bold small text-muted">Account Menu</span>
                    <button type="button" class="btn-close" style="font-size: 0.7rem;" data-bs-toggle="dropdown" aria-label="Close"></button>
                </li>
                <li><a class="dropdown-item rounded-3 py-2" href="profile.php"><i class="fa-regular fa-user me-2"></i> Account</a></li>
                <li><a class="dropdown-item rounded-3 py-2" href="settings.php"><i class="fa-solid fa-gear me-2"></i> Preferences</a></li>
                <li class="d-none d-md-block">
                    <hr class="dropdown-divider">
                </li>
                <li class="d-none d-md-block">
                    <a class="dropdown-item rounded-3 py-2 text-center small text-muted" href="#" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-chevron-up me-1"></i> Close Menu
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item rounded-3 py-2 text-danger" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid p-0">
            <div class="row mb-4 gs-anim">
                <div class="col-12">
                    <h2 class="fw-bold mb-1">Performance Data</h2>
                    <p class="text-muted small">Real-time statistics of your music platform.</p>
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-6 col-lg-3 gs-anim">
                    <div class="card-stats">
                        <p>Musics</p>
                        <h2 class="counter"><?php echo $musicCount; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-lg-3 gs-anim">
                    <div class="card-stats">
                        <p>Videos</p>
                        <h2 class="counter"><?php echo $videoCount; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-lg-3 gs-anim">
                    <div class="card-stats">
                        <p>Albums</p>
                        <h2 class="counter"><?php echo $albumCount; ?></h2>
                    </div>
                </div>
                <div class="col-6 col-lg-3 gs-anim">
                    <div class="card-stats">
                        <p>Users</p>
                        <h2 class="counter"><?php echo $userCount; ?></h2>
                    </div>
                </div>
            </div>

            <?php
            $sections = [
                ['title' => 'Recent Music Releases', 'icon' => 'fa-bolt text-warning', 'query' => $musicList, 'folder' => 'music_covers', 'key' => 'cover_image'],
                ['title' => 'Latest Video Premieres', 'icon' => 'fa-fire text-danger', 'query' => $videoList, 'folder' => 'video_thumbnails', 'key' => 'thumbnail'],
                ['title' => 'New Album Entries', 'icon' => 'fa-layer-group text-success', 'query' => $albumList, 'folder' => 'albums', 'key' => 'cover']
            ];
            foreach ($sections as $sec): ?>
                <h5 class="fw-bold mb-3 gs-anim mt-4"><i class="fa <?php echo $sec['icon']; ?> me-2"></i> <?php echo $sec['title']; ?></h5>
                <div class="swiper mySwiper gs-anim mb-5">
                    <div class="swiper-wrapper">
                        <?php mysqli_data_seek($sec['query'], 0);
                        while ($row = mysqli_fetch_assoc($sec['query'])) { ?>
                            <div class="swiper-slide"><img src="<?php echo getImagePath($row[$sec['key']], $sec['folder']); ?>" alt="Media"></div>
                        <?php } ?>
                    </div>
                    <div class="swiper-pagination mt-3"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <script>
        window.addEventListener('load', () => {
            gsap.to("#pageLoader", {
                opacity: 0,
                duration: 0.5,
                onComplete: () => {
                    document.getElementById('pageLoader').style.display = 'none';
                    gsap.from(".gs-anim", {
                        y: 30,
                        opacity: 0,
                        duration: 0.6,
                        stagger: 0.1
                    });
                }
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        new Swiper(".mySwiper", {
            slidesPerView: 1.2,
            spaceBetween: 15,
            autoplay: {
                delay: 3000
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true
            },
            breakpoints: {
                480: {
                    slidesPerView: 2.2
                },
                768: {
                    slidesPerView: 3.2
                },
                1024: {
                    slidesPerView: 4.5
                },
                1400: {
                    slidesPerView: 5.5
                }
            }
        });
    </script>
</body>

</html>