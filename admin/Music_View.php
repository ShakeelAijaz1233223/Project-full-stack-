<?php
include "db.php";

if (!isset($_SESSION['email'])) header("Location: login.php");

// Handle audio deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = mysqli_query($conn, "SELECT file FROM music WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        $filepath = "uploads/music/" . $row['file'];
        if (file_exists($filepath)) {
            unlink($filepath); // Delete audio file
        }
        mysqli_query($conn, "DELETE FROM music WHERE id=$id"); // Delete DB record
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Music Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f0f3f8;
            color: #fff;
            min-height: 100vh;
            font-family: Segoe UI, sans-serif;
            margin: 0;
            padding: 0;
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
            transition: 0.3s;
            z-index: 1000;
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

        /* ===== CONTENT ===== */
        .container {
            padding: 30px 40px;
            margin-left: 240px;
            transition: margin-left 0.3s;
        }

        /* SEARCH BOX */
        .search-box {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        /* CARD */
        .card {
            background: #2c2b2b;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transform: scale(1);
            transition: 0.4s;
            animation: fadeUp 0.7s ease;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        /* DELETE BUTTON */
 
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            /* on top of video */
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


        .audio-player {
            width: 100%;
            margin-bottom: 15px;
        }

        .title {
            font-size: 16px;
            margin-bottom: 6px;
        }

        .artist {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 6px;
        }

        .meta {
            font-size: 12px;
            opacity: 0.5;
        }

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

        footer {
            text-align: center;
            padding: 30px;
            opacity: 0.6;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .container {
                margin-left: 200px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar a {
                padding: 10px 5px;
                font-size: 12px;
            }

            .container {
                margin-left: 80px;
                padding: 15px;
            }

            .grid {
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .container {
                margin-left: 0;
                padding: 10px;
            }

            .grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .audio-player {
                width: 100%;
            }

            .search-box {
                width: 100%;
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="add_music.php">⬆ Upload Music</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="container">
        <input type="text" id="search" class="search-box" placeholder="🔍 Search music title or artist">
        <div class="grid" id="musicGrid">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM music ORDER BY id DESC");
            while ($row = mysqli_fetch_assoc($res)) {
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $file = $row['file'];
                $id = $row['id'];
                echo '
            <div class="card" data-title="' . strtolower($title) . '" data-artist="' . strtolower($artist) . '">
                <!-- DELETE BUTTON -->
                <form method="GET" onsubmit="return confirm(\'Delete this music?\');">
                    <input type="hidden" name="delete" value="' . $id . '">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>

                <audio class="audio-player" controls>
                    <source src="uploads/music/' . $file . '" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <div class="title">' . $title . '</div>
                <div class="artist">' . $artist . '</div>
                <div class="meta">Uploaded</div>
            </div>
            ';
            }
            ?>
        </div>
    </div>

    <footer>
        © 2026 Music Platform • Fully Animated Page
    </footer>

    <script>
        // ===== SEARCH FUNCTIONALITY =====
        document.getElementById("search").addEventListener("keyup", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".card").forEach(card => {
                let title = card.dataset.title;
                let artist = card.dataset.artist;
                card.style.display = (title.includes(val) || artist.includes(val)) ? "block" : "none";
            });
        });
    </script>

</body>

</html>