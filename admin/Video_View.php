<?php
include "db.php";

// Ensure session is active and user is logged in
if (!isset($_SESSION['email'])) header("Location: login.php");

// Handle video deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT file FROM videos WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        $filepath = "uploads/videos/" . $row['file'];
        if (file_exists($filepath)) {
            unlink($filepath); // Delete the video file
        }
        mysqli_query($conn, "DELETE FROM videos WHERE id=$id"); // Delete DB record
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Video Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            background: #f0f3f8;
            color: #fff;
            min-height: 100vh;
            font-family: Segoe UI, sans-serif;
            margin: 0;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100vh;
            background: linear-gradient(180deg, #1f2a48, #3e4a76);
            padding: 20px;
            box-sizing: border-box;
        }

        .sidebar a {
            display: block;
            color: #fff;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 12px;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        /* ===== VIDEO GRID ===== */
        .container {
            padding: 30px 20px;
            margin-left: 240px;
            box-sizing: border-box;
        }

        .search-box {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        .card {
            background: #2c2b2b;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transform: scale(1);
            transition: 0.4s;
            animation: fadeUp 0.7s ease;
            position: relative;
        }

        .thumbnail {
            height: 160px;
            background: #2c2b2b;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        .thumbnail video {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            transition: 0.3s;
        }

        .info {
            padding: 15px;
        }

        .title {
            font-size: 16px;
            margin-bottom: 6px;
            word-wrap: break-word;
        }

        .meta {
            font-size: 13px;
            opacity: 0.7;
        }

        /* ===== DELETE BUTTON ===== */
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            background: red;
            color: #fff;
            border: none;
            padding: 5px 8px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            opacity: 0.8;
            transition: 0.3s;
        }

        .delete-btn:hover {
            opacity: 1;
        }

        /* ===== ANIMATION ===== */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 30px;
            opacity: 0.6;
        }

        /* ===== RESPONSIVE ===== */
        @media(max-width:768px) {
            .container {
                margin-left: 0;
                padding: 20px 10px;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .thumbnail {
                height: auto;
            }

            video {
                max-height: 200px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="add_video.php">⬆ Upload Video</a>
        <a href="#">❓ Help</a>
    </div>

    <!-- CONTENT -->
    <div class="container">
        <input type="text" id="search" class="search-box" placeholder="🔍 Search video title">
        <div class="grid" id="videoGrid">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM videos ORDER BY id DESC");
            while ($row = mysqli_fetch_assoc($res)) {
                $title = htmlspecialchars($row['title']);
                $file  = $row['file'];
                $id    = $row['id'];
                echo '
            <div class="card" data-title="' . strtolower($title) . '">
                <div class="thumbnail">
                    <!-- DELETE BUTTON ON TOP OF VIDEO -->
                    <form method="GET" onsubmit="return confirm(\'Delete this video?\');">
                        <input type="hidden" name="delete" value="' . $id . '">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>

                    <!-- Updated video player -->
                    <video src="uploads/videos/' . $file . '" controls></video>
                </div>
                <div class="info">
                    <div class="title">' . $title . '</div>
                    <div class="meta">Uploaded</div>
                </div>  
            </div>
            ';
            }
            ?>
        </div>
    </div>

    <footer>
        © 2026 Video Platform • Fully Animated Page
    </footer>

    <script>
        // ===== SEARCH FUNCTIONALITY =====
        document.getElementById("search").addEventListener("keyup", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".card").forEach(card => {
                card.style.display = card.dataset.title.includes(val) ? "block" : "none";
            });
        });

        // ===== PLAY VIDEO ON HOVER =====
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('mouseenter', () => {
                video.play();  // Start playing when hovered
            });
            video.addEventListener('mouseleave', () => {
                // Continue playing without stopping
            });
        });
    </script>

</body>

</html>
