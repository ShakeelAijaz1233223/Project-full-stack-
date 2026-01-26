<?php
// Define the getImagePath function at the top
function getImagePath($imageName, $defaultImage) {
    $imagePath = 'uploads/' . $imageName;
    return (file_exists($imagePath) && !empty($imageName)) ? $imagePath : 'uploads/' . $defaultImage;
}

// Include the database connection
include "db.php";

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch counts
$musicCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM music"))['total'];
$videoCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM videos"))['total'];
$albumCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM albums"))['total'];
$userCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];

// Fetch user info
$userEmail = $_SESSION['email'];
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE email='$userEmail'");
$userData  = mysqli_fetch_assoc($userQuery);

// Profile image (from avatar column)
$profileImg = !empty($userData['avatar']) && file_exists('uploads/' . $userData['avatar'])
    ? $userData['avatar']
    : 'default.png';

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

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Swiper Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6fa;
            overflow-x: hidden;
            opacity: 0;
            animation: fadeIn 1s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: linear-gradient(180deg, #1f2a48, #3e4a76);
            color: #fff;
            padding: 25px 15px;
            overflow-y: auto;
            transition: all 0.4s ease;
        }

        .sidebar h2 {
            font-size: 26px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            /* animation: logoBounce 2s infinite alternate; */
        }

        .sidebar h2 i {
            animation: beatPulse 0.9s infinite ease-in-out;
        }

        @keyframes beatPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.25);
            }

            100% {
                transform: scale(1)
            }
        }

        


        @keyframes logoBounce {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px) rotate(-5deg);
            }

            100% {
                transform: translateY(0);
            }
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 15px;
            color: #cfd8e1;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            width: 25px;
            font-size: 18px;
            transition: transform 0.3s;
        }

        .sidebar a:hover i {
            transform: rotate(360deg);
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .sidebar a.text-danger {
            color: #ff6b6b;
        }

        .sidebar a.text-danger:hover {
            background: rgba(255, 107, 107, 0.2);
        }

        .top-navbar {
            position: fixed;
            left: 250px;
            right: 0;
            height: 70px;
            background: #fff;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .top-navbar img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .top-navbar img:hover {
            transform: scale(1.15) rotate(10deg);
        }

        .main-content {
            margin-left: 250px;
            padding: 100px 50px 50px 50px;
        }

        .main-content h1 {
            font-size: 32px;
            color: #1f2a48;
            margin-bottom: 30px;
        }

        .card-stats {
            background: #fff;
            padding: 30px 20px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .card-stats:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-stats p {
            font-size: 16px;
            color: #7a8ca1;
            margin-bottom: 12px;
        }

        .card-stats h3 {
            font-size: 32px;
            color: #1f2a48;
        }

        .counter {
            font-weight: 700;
        }

        .swiper {
            width: 100%;
            padding-top: 30px;
            padding-bottom: 30px;
        }

        .swiper-slide {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .swiper-slide img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 15px;
        }

        .swiper-slide:hover {
            transform: scale(1.05);
        }

        @media(max-width:992px) {
            .sidebar {
                width: 220px;
            }

            .top-navbar {
                left: 220px;
            }

            .main-content {
                margin-left: 220px;
                padding: 90px 30px 30px 30px;
            }
        }

        @media(max-width:768px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding-bottom: 20px;
            }

            .top-navbar {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: 120px 20px 20px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2><i class="fa-solid fa-music"></i> Music Admin</h2>
        <a href="dashboard.php"><i class="fa fa-house"></i> Dashboard</a>
        <a href="profile.php"><i class="fa fa-user-circle"></i> Profile</a>
        <a href="user.php"><i class="fa fa-users"></i> Users</a>
        <a href="Music_View.php"><i class="fa fa-music"></i> Musics</a>
        <a href="Video_View.php"><i class="fa fa-video"></i> Videos</a>
        <a href="albums_View.php"><i class="fa fa-compact-disc"></i> Albums</a>
        <a href="settings.php"><i class="fa fa-cogs"></i> Settings</a>
        <a href="reports.php"><i class="fa fa-sliders-h""></i> Reports</a>
    <hr style=" border-color: rgba(255,255,255,0.2); margin: 15px 0;">
                <a href="add_albums.php"><i class="fa fa-compact-disc"></i> Albums</a>
               
                <a href="add_music.php"><i class="fa fa-upload"></i> Upload Music</a>
                <a href="add_video.php"><i class="fa fa-video"></i> Upload Video</a>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 15px 0;">
                <a href="logout.php" class="text-danger"><i class="fa fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="top-navbar">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo 'uploads/' . $profileImg; ?>" alt="Profile">
                <span class="ms-2"><?php echo htmlspecialchars($userName); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="profileDropdown" style="min-width:220px;">
                <li class="d-flex align-items-center mb-2">
                    <img src="<?php echo 'uploads/' . $profileImg; ?>" alt="Profile" class="rounded-circle me-2" style="width:50px;height:50px;object-fit:cover;">
                    <div>
                        <strong><?php echo htmlspecialchars($userName); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($_SESSION['email']); ?></small>
                    </div>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="profile.php"><i class="fa fa-user-circle me-2"></i> Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fa fa-cogs me-2"></i> Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- PAGE LOADER (ADD ONLY) -->
    <div id="pageLoader">
        <div class="loader"></div>
    </div>


    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card-stats">
                    <p>Total Music</p>
                    <h3 class="counter"><?php echo $musicCount; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stats">
                    <p>Total Videos</p>
                    <h3 class="counter"><?php echo $videoCount; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stats">
                    <p>Total Albums</p>
                    <h3 class="counter"><?php echo $albumCount; ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-stats">
                    <p>Total Users</p>
                    <h3 class="counter"><?php echo $userCount; ?></h3>
                </div>
            </div>
        </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        // Counter animation
        document.querySelectorAll('.counter').forEach(counter => {
            const updateCount = () => {
                const target = +counter.innerText;
                let count = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    count += increment;
                    if (count >= target) {
                        counter.innerText = target;
                        clearInterval(timer);
                    } else {
                        counter.innerText = Math.ceil(count);
                    }
                }, 15);
            };
            updateCount();
        });

        
        ['Music', 'Video', 'Album'].forEach(type => {
            new Swiper(`.mySwiper${type}`, {
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                navigation: {
                    nextEl: `.mySwiper${type} .swiper-button-next`,
                    prevEl: `.mySwiper${type} .swiper-button-prev`
                },
                pagination: {
                    el: `.mySwiper${type} .swiper-pagination`,
                    clickable: true
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2
                    },
                    992: {
                        slidesPerView: 3
                    }
                }
            });
        });

        window.onload = function() {
    const loader = document.getElementById('pageLoader');
    loader.style.display = 'none';
};


    // Swiper Initialization
document.addEventListener("DOMContentLoaded", function () {
    ['Music', 'Video', 'Album'].forEach(type => {
        new Swiper(`.mySwiper${type}`, {
            slidesPerView: 1,
            spaceBetween: 15,
            loop: true,
            navigation: {
                nextEl: `.mySwiper${type} .swiper-button-next`,
                prevEl: `.mySwiper${type} .swiper-button-prev`
            },
            pagination: {
                el: `.mySwiper${type} .swiper-pagination`,
                clickable: true
            },
            breakpoints: {
                768: {
                    slidesPerView: 2
                },
                992: {
                    slidesPerView: 3
                }
            }
        });
    });
});

// Page loader hide
window.onload = function() {
    const loader = document.getElementById('pageLoader');
    loader.style.display = 'none'; // Hide the loader after content is loaded
};


    </script>
</body>

</html>