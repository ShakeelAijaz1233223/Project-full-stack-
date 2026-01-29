<?php
include "../config/db.php";

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
    <title>Video Gallery | Pro Studio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-color: #1e293b;
            --accent-color: #3b82f6;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: var(--bg-color);
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: var(--sidebar-color);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px 15px;
            box-sizing: border-box;
            z-index: 1000;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 10px;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .sidebar a:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        /* ===== MAIN CONTENT ===== */
        .container {
            padding: 40px;
            margin-left: 240px;
            transition: margin-left var(--transition);
        }

        .search-box {
            width: 100%;
            max-width: 450px;
            padding: 12px 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 16px;
            margin-bottom: 35px;
            outline: none;
            transition: var(--transition);
        }

        .search-box:focus {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        /* ===== VIDEO GRID ===== */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            position: relative;
            transition: var(--transition);
            animation: fadeUp 0.6s ease-out;
        }

        .card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .thumbnail {
            width: 100%;
            aspect-ratio: 16 / 9;
            background: #000;
            position: relative;
            overflow: hidden;
        }

        .thumbnail video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .info {
            padding: 18px;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* ===== DELETE BUTTON (Browser-Safe Hover) ===== */
        .delete-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 20;
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            opacity: 0;
            transition: var(--transition);
            -webkit-appearance: none;
        }

        .card:hover .delete-btn {
            opacity: 1;
        }

        .delete-btn:hover {
            background: #ef4444;
            transform: scale(1.05);
        }

        /* ===== ANIMATION ===== */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }

            .sidebar a span {
                display: none;
            }

            .container {
                margin-left: 80px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }

            .container {
                margin-left: 0;
                padding: 20px;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }

        footer {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <a href="dashboard.php">🏠 <span>Dashboard</span></a>
        <a href="add_video.php">⬆ <span>Upload Video</span></a>
        <a href="logout.php"><i class="fa fa-sign-out-alt"></i> <span>Logout</span></a> 
    </div>

    <div class="container">
        <input type="text" id="search" class="search-box" placeholder="Search your library...">

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
                        <form method="GET" onsubmit="return confirm(\'Delete this video?\');">
                            <input type="hidden" name="delete" value="' . $id . '">
                            <button type="submit" class="delete-btn">DELETE</button>
                        </form>
                        <video src="uploads/videos/' . $file . '" preload="metadata" playsinline></video>
                    </div>
                    <div class="info">
                        <div class="title">' . $title . '</div>
                        <div class="meta">Recently Uploaded</div>
                    </div>  
                </div>';
            }
            ?>
        </div>
    </div>

    <footer>
        © 2026 Studio Platform • Optimised for Modern Browsers
    </footer>

    <script>
        // Smooth Search Filter
        document.getElementById("search").addEventListener("input", function() {
            const val = this.value.toLowerCase();
            const cards = document.querySelectorAll(".card");

            cards.forEach(card => {
                const isMatch = card.dataset.title.includes(val);
                card.style.display = isMatch ? "block" : "none";
            });
        });

        const videoCards = document.querySelectorAll('.card');

videoCards.forEach(card => {
    const video = card.querySelector('video');
    if(!video) return;

    // Start with controls hidden
    video.controls = false;

    // Click anywhere on the video toggles play/pause and shows controls
    video.addEventListener('click', (e) => {
        e.stopPropagation();

        if(video.paused) {
            video.play().catch(() => console.log("Playback blocked by browser"));
        } else {
            video.pause();
        }

        // Toggle controls visibility
        video.controls = !video.controls;
    });
});

    </script>
</body>

</html>