<?php
// Define the getImagePath function
function getImagePath($imageName, $defaultImage)
{
    $imagePath = 'uploads/' . $imageName;
    return (file_exists($imagePath) && !empty($imageName)) ? $imagePath : 'uploads/' . $defaultImage;
}

include "../config/db.php";

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch counts
$musicCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM music"))['total'] ?? 0;
$videoCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM videos"))['total'] ?? 0;
$albumCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM albums"))['total'] ?? 0;
$userCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;

// Fetch user info
$userEmail = $_SESSION['email'];
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE email='$userEmail'");
$userData  = mysqli_fetch_assoc($userQuery);

$profileImg = !empty($userData['avatar']) && file_exists('uploads/' . $userData['avatar']) ? $userData['avatar'] : 'default.png';
$userName = !empty($userData['name']) ? $userData['name'] : 'Admin';

// Fetch images for sliders
$musicImages = mysqli_query($conn, "SELECT cover_image FROM music ORDER BY id DESC LIMIT 5");
$videoImages = mysqli_query($conn, "SELECT thumbnail FROM videos ORDER BY id DESC LIMIT 5");
$albumImages = mysqli_query($conn, "SELECT cover_image FROM albums ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #1e1e2f;
            --accent-color: #e14eca;
            --body-bg: #f8f9fe;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--body-bg);
            overflow-x: hidden;
        }

        /* --- PAGE LOADER --- */
        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* --- SIDEBAR --- */
        .sidebar {
            position: fixed;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            padding: 20px;
            transition: 0.4s;
            z-index: 1000;
        }

        .sidebar h2 {
            color: #fff;
            font-weight: 600;
            font-size: 22px;
            margin-bottom: 30px;
        }

        .sidebar h2 i {
            color: var(--accent-color);
            animation: beatPulse 1.2s infinite;
        }

        .sidebar a {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar a i {
            margin-right: 15px;
            width: 20px;
        }

        /* --- TOP NAV --- */
        .top-navbar {
            position: fixed;
            left: 260px;
            right: 0;
            height: 70px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 40px;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            padding: 100px 40px 40px;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- STAT CARDS --- */
        .card-stats {
            background: #fff;
            border: none;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-stats::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent-color);
        }

        /* --- SWIPER SLIDER --- */
        .swiper {
            padding: 20px 0 50px;
        }

        .swiper-slide {
            border-radius: 15px;
            overflow: hidden;
            height: 200px;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes beatPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
            }

            .main-content {
                margin-left: 0;
            }

            .top-navbar {
                left: 0;
            }
        }
    </style>
</head>

<body>

    <div id="pageLoader">
        <div class="text-center">
            <div class="loader mb-3"></div>
            <p class="text-muted fw-bold">Loading Dashboard...</p>
        </div>
    </div>

    <div class="sidebar">

        <h2><i class="fa-solid fa-music"></i> Music Admin</h2>

        <a href="dashboard.php"><i class="fa fa-house"></i> Dashboard</a>

        <a href="profile.php"><i class="fa fa-user-circle"></i> Profile</a>

        <a href="user.php"><i class="fa fa-users"></i> Users</a>

        <a href="Music_View.php"><i class="fa fa-music"></i> Musics</a>

        <a href="Video_View.php"><i class="fa fa-video"></i> Videos</a>

        <a href="albums_View.php"><i class="fa fa-compact-disc"></i> Albums</a>

        <a href="settings.php"><i class="fa fa-cogs"></i> Settings</a>



        <hr style=" border-color: rgba(255,255,255,0.2); margin: 15px 0;">

        <a href="add_albums.php"><i class="fa fa-compact-disc"></i> Albums</a>



        <a href="add_music.php"><i class="fa fa-upload"></i> Upload Music</a>

        <a href="add_video.php"><i class="fa fa-video"></i> Upload Video</a>

        <hr style="border-color: rgba(255,255,255,0.2); margin: 15px 0;">

        <a href="logout.php" class="text-danger"><i class="fa fa-right-from-bracket"></i> Logout</a>

    </div>

    <div class="top-navbar">
        <div class="dropdown">
            <div class="d-flex align-items-center cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                <div class="text-end me-3 d-none d-md-block">
                    <p class="mb-0 fw-bold"><?php echo htmlspecialchars($userName); ?></p>
                    <small class="text-muted">Administrator</small>
                </div>
                <img src="uploads/<?php echo $profileImg; ?>" class="rounded-circle border" width="45" height="45">
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user me-2"></i> Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fa fa-cog me-2"></i> Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-power-off me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <h1 class="fw-bold mb-4">Dashboard Overview</h1>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card-stats">
                    <p class="text-muted text-uppercase small fw-bold">Total Music</p>
                    <h2 class="counter fw-bold mb-0"><?php echo $musicCount; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stats">
                    <p class="text-muted text-uppercase small fw-bold">Total Videos</p>
                    <h2 class="counter fw-bold mb-0"><?php echo $videoCount; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stats">
                    <p class="text-muted text-uppercase small fw-bold">Total Albums</p>
                    <h2 class="counter fw-bold mb-0"><?php echo $albumCount; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stats">
                    <p class="text-muted text-uppercase small fw-bold">Total Users</p>
                    <h2 class="counter fw-bold mb-0"><?php echo $userCount; ?></h2>
                </div>
            </div>
        </div>

        <h3 class="fw-bold mb-3"><i class="fa fa-clock me-2 text-primary"></i> Recently Added Music</h3>
        <div class="swiper mySwiperMusic">
            <div class="swiper-wrapper">
                <?php while ($row = mysqli_fetch_assoc($musicImages)): ?>
                    <div class="swiper-slide shadow-sm">
                        <img src="uploads/<?php echo $row['cover_image']; ?>" alt="Music">
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <script>
        // Smooth Page Loader
        window.addEventListener('load', () => {
            const loader = document.getElementById('pageLoader');
            loader.style.opacity = '0';
            setTimeout(() => loader.style.display = 'none', 500);
        });

        // Enhanced Counter Animation
        document.querySelectorAll('.counter').forEach(counter => {
            const target = +counter.innerText;
            const duration = 1500; // 1.5 seconds
            let start = 0;
            const increment = target / (duration / 16);

            const updateCount = () => {
                start += increment;
                if (start < target) {
                    counter.innerText = Math.ceil(start);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });

        // Swiper Initialization
        new Swiper(".mySwiperMusic", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true
            },
            breakpoints: {
                640: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 4
                }
            }
        });
    </script>
</body>

</html>