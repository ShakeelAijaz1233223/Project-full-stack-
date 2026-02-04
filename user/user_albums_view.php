<?php
session_start();
include "../config/db.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $album = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM albums WHERE id=$delete_id"));
    if ($album) {
        @unlink("../admin/uploads/albums/" . $album['cover']);
        @unlink("../admin/uploads/albums/" . $album['audio']);
        @unlink("../admin/uploads/albums/" . $album['video']);
        mysqli_query($conn, "DELETE FROM albums WHERE id=$delete_id");
        $msg = "Album deleted successfully!";
    }
}

// Fetch albums
$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #050505;
            --card-bg: #0a0a0a;
            --accent: #ff0055;
            --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
        }

        body {
            background-color: var(--bg-dark);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
        }

        header {
            background: rgba(5, 5, 5, 0.9);
            backdrop-filter: blur(20px);
            padding: 15px 5%;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logo {
            font-family: 'Syncopate', sans-serif;
            font-size: 1.2rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: 3px;
        }
        .logo span { color: var(--accent); }

        .search-box {
            background: #151515;
            border: 1px solid #222;
            color: white;
            border-radius: 50px;
            padding: 6px 18px;
            width: 220px;
            font-size: 0.8rem;
        }

        .studio-wrapper {
            width: 90%;
            margin: 0 auto;
            padding: 40px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .album-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: 0.3s ease;
            position: relative;
        }

        .album-card:hover {
            border-color: var(--accent);
            transform: translateY(-5px);
        }

        .media-container {
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .media-container video, .media-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* --- PROGRESS LINE --- */
        .progress-container {
            width: 100%;
            margin: 15px 0 10px;
        }

        .seek-bar {
            width: 100%;
            height: 4px;
            -webkit-appearance: none;
            background: #222;
            border-radius: 10px;
            cursor: pointer;
            outline: none;
        }

        .seek-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 12px;
            height: 12px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--accent);
        }

        /* --- CONTROLS --- */
        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .play-btn {
            width: 45px;
            height: 45px;
            background: var(--accent-gradient);
            border: none;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
        }

        .nav-btn { background: none; border: none; color: #555; font-size: 1.2rem; cursor: pointer; }
        .nav-btn:hover { color: #fff; }

        .title { font-weight: 700; font-size: 0.95rem; margin: 8px 0 2px; text-align: center; }
        .artist { font-size: 0.75rem; color: #555; text-transform: uppercase; text-align: center; margin-bottom: 10px; }

        .card-actions {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: 0.3s;
        }
        .album-card:hover .card-actions { opacity: 1; }

        .action-btn {
            background: rgba(0,0,0,0.8);
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.8rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        footer { padding: 40px; text-align: center; font-size: 0.7rem; color: #333; letter-spacing: 2px; }
    </style>
</head>

<body>

    <header>
        <a href="index.php" class="logo">SOU<span>N</span>D</a>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search albums...">
            <a href="index.php" style="color:#fff;"><i class="bi bi-arrow-left"></i></a>
        </div>
    </header>

    <div class="studio-wrapper">
        <div class="mb-4">
            <h4 class="fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success bg-dark text-success border-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="grid" id="albumGrid">
            <?php if (mysqli_num_rows($albums) > 0):
                while ($row = mysqli_fetch_assoc($albums)):
                    $id = $row['id'];
                    $title = htmlspecialchars($row['title']);
                    $artist = htmlspecialchars($row['artist']);
                    $audio = $row['audio'];
                    $video = $row['video'];
                    $cover = $row['cover'];
            ?>
                <div class="album-card" data-title="<?= strtolower($title); ?>" data-artist="<?= strtolower($artist); ?>">
                    
                    <div class="card-actions">
                        <a href="edit_album.php?id=<?= $id; ?>" class="action-btn"><i class="bi bi-pencil"></i></a>
                        <a href="?delete=<?= $id; ?>" class="action-btn" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                    </div>

                    <div class="media-container">
                        <?php if (!empty($video)): ?>
                            <video id="vid-<?= $id ?>" preload="metadata" playsinline loop>
                                <source src="../admin/uploads/albums/<?= $video; ?>" type="video/mp4">
                            </video>
                        <?php else: ?>
                            <img src="../admin/uploads/albums/<?= $cover; ?>" alt="Cover">
                        <?php endif; ?>
                    </div>

                    <div class="title"><?= $title; ?></div>
                    <div class="artist"><?= $artist; ?></div>

                    <div class="progress-container">
    <input type="range" class="seek-bar" 
           id="seek-<?= $id ?>" 
           value="0" min="0" max="100" 
           oninput="seekMedia(this, '<?= $id ?>')" 
           onchange="seekMedia(this, '<?= $id ?>')">
</div>

                    <div class="controls">
                        <button class="nav-btn" onclick="skipMedia('<?= $id ?>', -10)"><i class="bi bi-rewind-fill"></i></button>
                        <button class="play-btn" onclick="togglePlayback('<?= $id ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                        <button class="nav-btn" onclick="skipMedia('<?= $id ?>', 10)"><i class="bi bi-fast-forward-fill"></i></button>
                    </div>

                    <audio id="aud-<?= $id ?>" ontimeupdate="updateProgress('<?= $id ?>')">
                        <source src="../admin/uploads/albums/<?= $audio; ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endwhile; else: ?>
                <p class="text-muted">No Albums Found</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>SOUND ENTERTAINMENT &bull; 2026</footer>

    <script>
        // Search
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase().trim();
            document.querySelectorAll(".album-card").forEach(card => {
                let text = card.dataset.title + " " + card.dataset.artist;
                card.style.display = text.includes(val) ? "block" : "none";
            });
        });

        // Combined Playback Logic
        function togglePlayback(id, btn) {
            const audio = document.getElementById('aud-' + id);
            const video = document.getElementById('vid-' + id);
            const icon = btn.querySelector('i');

            if (audio.paused) {
                // Stop others
                document.querySelectorAll('audio, video').forEach(m => m.pause());
                document.querySelectorAll('.play-btn i').forEach(i => i.className = 'bi bi-play-fill');

                audio.play();
                if (video) {
                    video.currentTime = audio.currentTime;
                    video.play();
                    video.muted = false; // Ensures sound is active
                }
                icon.className = 'bi bi-pause-fill';
            } else {
                audio.pause();
                if (video) video.pause();
                icon.className = 'bi bi-play-fill';
            }
        }

        // Move Line (Progress)
        function updateProgress(id) {
            const audio = document.getElementById('aud-' + id);
            const card = audio.closest('.album-card');
            const seekBar = card.querySelector('.seek-bar');
            const percentage = (audio.currentTime / audio.duration) * 100;
            seekBar.value = percentage || 0;
        }

        // Seek (Ahgi / Pisha manual move)
        function seekMedia(slider, id) {
            const audio = document.getElementById('aud-' + id);
            const video = document.getElementById('vid-' + id);
            const seekTo = (slider.value / 100) * audio.duration;
            audio.currentTime = seekTo;
            if (video) video.currentTime = seekTo;
        }

        // Skip
        function skipMedia(id, secs) {
            const audio = document.getElementById('aud-' + id);
            const video = document.getElementById('vid-' + id);
            audio.currentTime += secs;
            if (video) video.currentTime = audio.currentTime;
        }
    </script>
</body>
</html>