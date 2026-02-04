<?php
session_start();
include "../config/db.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $album = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM albums WHERE id=$delete_id"));
    if ($album) {
        // Delete files safely
        @unlink("../admin/uploads/albums/" . $album['cover']);
        @unlink("../admin/uploads/albums/" . $album['audio']);
        @unlink("../admin/uploads/albums/" . $album['video']);

        // Delete DB record
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
    <title>Albums Studio | Compact Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg-dark: #080808;
            --card-bg: #121212;
            --accent: #ff0055;
            --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #888888;
        }

        body {
            background-color: var(--bg-dark);
            color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 20px 0;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 15px;
        }

        .search-box {
            background: #1a1a1a;
            border: 1px solid #222;
            color: white;
            border-radius: 4px;
            padding: 6px 15px;
            width: 250px;
            font-size: 0.85rem;
        }

        .btn-back,
        .btn-delete,
        .btn-edit {
            background: #1a1a1a;
            border: 1px solid #222;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.3s ease;
        }

        .btn-back:hover,
        .btn-delete:hover,
        .btn-edit:hover {
            background: #222;
            border-color: var(--accent);
            color: #fff;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid transparent;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.3s ease;
            text-align: center;
            padding: 10px;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            background: #1a1a1a;
            border-color: #333;
        }

        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        audio {
            width: 100%;
            height: 25px;
            margin-top: 8px;
            filter: invert(1) hue-rotate(180deg) brightness(1.2);
            opacity: 0.4;
        }

        .card:hover audio {
            opacity: 1;
        }

        .play-overlay {
            position: absolute;
            width: 35px;
            height: 35px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 10;
            border: none;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .card:hover .play-overlay {
            opacity: 1;
        }
  /* --- NAVIGATION CONTROLS --- */
        .controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        .card-body {
            padding: 5px 0;
        }

        .title {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 1px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 6px;
        }

        footer {
            padding: 40px;
            text-align: center;
            font-size: 0.7rem;
            color: #444;
            letter-spacing: 1px;
        }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search albums...">
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="grid" id="albumGrid">
            <?php if (mysqli_num_rows($albums) > 0):
                while ($row = mysqli_fetch_assoc($albums)):
                    $title = htmlspecialchars($row['title']);
                    $artist = htmlspecialchars($row['artist']);
                    $audio = $row['audio'];
                    $video = $row['video'];
                    $cover = $row['cover'];
                    $id = $row['id'];
                    $mime = '';
                    if (!empty($video)) {
                        $ext = strtolower(pathinfo($video, PATHINFO_EXTENSION));
                        $mime = match ($ext) {
                            'mp4' => 'video/mp4',
                            'webm' => 'video/webm',
                            'ogv' => 'video/ogg',
                            default => 'video/mp4'
                        };
                    }
            ?>
                    <div class="card album-card" data-title="<?php echo strtolower($title); ?>" data-artist="<?php echo strtolower($artist); ?>">
                        <div class="card-actions">
                            <a href="?delete=<?php echo $id; ?>" class="btn-delete" onclick="return confirm('Delete this album?');"><i class="bi bi-trash"></i></a>
                            <a href="edit_album.php?id=<?php echo $id; ?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
                        </div>

                        <div class="media-wrapper">
                            <?php if (!empty($video)): ?>
                                <video id="vid-<?php echo $id; ?>" preload="metadata" playsinline muted loop poster="../admin/uploads/albums/<?php echo $cover; ?>">
                                    <source src="../admin/uploads/albums/<?php echo $video; ?>" type="<?php echo $mime; ?>">
                                    Your browser does not support video.
                                </video>
                                <button class="play-overlay" onclick="togglePlay('<?php echo $id; ?>', this)">
                                    <i class="bi bi-play-fill"></i>
                                </button>
                            <?php else: ?>
                                <img src="../admin/uploads/albums/<?php echo $cover; ?>" alt="<?php echo $title; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="title"><?php echo $title; ?></div>
                            <div class="artist"><?php echo $artist; ?></div>
                            <?php if (!empty($audio)): ?>
                                <div class="controls">
                    <button class="nav-btn" onclick="skip(this, -10)">
                        <i class="bi bi-rewind-fill"></i>
                    </button>
                    
                    <button class="play-btn" onclick="toggleMusic(this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    
                    <button class="nav-btn" onclick="skip(this, 10)">
                        <i class="bi bi-fast-forward-fill"></i>
                    </button>
                </div>
                                <audio id="aud-<?php echo $id; ?>" onplay="handleMediaPlay(this)" onpause="handleMediaPause(this)">
                                    <source src="../admin/uploads/albums/<?php echo $audio; ?>" type="audio/mpeg">
                                </audio>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="text-center w-100 py-5">
                    <p class="text-muted">Empty Studio</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>&copy; 2026 ALBUMS STUDIO &bull; SOUND SYSTEM</footer>

    <script>
        // ===============================
        // REAL-TIME SEARCH
        // ===============================
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase().trim();
            document.querySelectorAll(".album-card").forEach(card => {
                let title = card.dataset.title;
                let artist = card.dataset.artist;
                card.style.display = (title.includes(val) || artist.includes(val)) ? "block" : "none";
            });
        });

        // ===============================
        // MASTER PLAY / PAUSE
        // ===============================
        function togglePlay(id, btn) {
            const audio = document.getElementById('aud-' + id);
            const video = document.getElementById('vid-' + id);
            const icon = btn.querySelector('i');

            // Stop other media
            document.querySelectorAll('audio, video').forEach(m => {
                if (m !== audio && m !== video) m.pause();
            });
            document.querySelectorAll('.play-overlay i').forEach(i => i.className = 'bi bi-play-fill');

            if (audio) {
                if (audio.paused) {
                    audio.play().catch(() => {});
                    if (video) {
                        video.muted = true;
                        video.currentTime = audio.currentTime;
                        video.play();
                    }
                    icon.className = 'bi bi-pause-fill';
                } else {
                    audio.pause();
                    if (video) video.pause();
                    icon.className = 'bi bi-play-fill';
                }
            } else if (video) {
                if (video.paused) {
                    video.muted = false;
                    video.play();
                    icon.className = 'bi bi-pause-fill';
                } else {
                    video.pause();
                    icon.className = 'bi bi-play-fill';
                }
            }
        }


        // ===============================
        // STOP OTHER AUDIO WHEN ONE PLAYS
        // ===============================
        function handleMediaPlay(current) {
            document.querySelectorAll('audio').forEach(a => {
                if (a !== current) a.pause();
            });
        }

        function handleMediaPause(current) {}
    </script>

</body>

</html>