<?php
session_start();
include "../config/db.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $album = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM albums WHERE id=$delete_id"));
    if ($album) {
        @unlink("../admin/uploads/albums/" . $album['cover']);
        @unlink("../admin/uploads/albums/" . $audio_file);
        mysqli_query($conn, "DELETE FROM albums WHERE id=$delete_id");
        header("Location: albums.php?msg=Deleted");
        exit();
    }
}

$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Pro | Music Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');

        :root {
            --bg-main: #0a0a0c;
            --sidebar-bg: #000000;
            --card-bg: #16161a;
            --accent: #ff0055;
            --accent-glow: rgba(255, 0, 85, 0.5);
            --text-dim: #a0a0a0;
        }

        body {
            background-color: var(--bg-main);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* --- Full Page Layout --- */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Design */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid #1a1a1a;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        /* Branding */
        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -1px;
            margin-bottom: 40px;
            display: block;
            text-decoration: none;
            color: #fff;
        }
        .logo span { color: var(--accent); }

        /* Search Bar Upgrade */
        .search-container {
            position: relative;
            margin-bottom: 40px;
        }
        .search-container input {
            background: #16161a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 12px 20px 12px 45px;
            color: white;
            width: 100%;
            transition: 0.3s;
        }
        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
        }

        /* Immersive Album Grid */
        .album-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }

        /* The Pro Card */
        .album-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 16px;
            transition: 0.4s all cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            position: relative;
        }

        .album-card:hover {
            background: #1f1f25;
            transform: translateY(-10px);
            border-color: var(--accent);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        .media-box {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 14px;
            overflow: hidden;
            background: #000;
            margin-bottom: 15px;
        }

        .media-box img, .media-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Floating Controls */
        .play-trigger {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            border: none;
            opacity: 0;
            transform: translateY(10px);
            transition: 0.3s;
            box-shadow: 0 8px 15px var(--accent-glow);
        }

        .album-card:hover .play-trigger {
            opacity: 1;
            transform: translateY(0);
        }

        /* Metadata */
        .album-info .title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .album-info .artist {
            color: var(--text-dim);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Admin Actions */
        .admin-tools {
            position: absolute;
            top: 25px;
            right: 25px;
            display: flex;
            gap: 8px;
            z-index: 5;
            opacity: 0;
            transition: 0.2s;
        }
        .album-card:hover .admin-tools { opacity: 1; }

        .tool-btn {
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .tool-btn:hover { background: var(--accent); color: white; }

        /* Custom Audio Player Styling */
        audio {
            width: 100%;
            height: 35px;
            margin-top: 15px;
            filter: invert(1) brightness(1.5);
            opacity: 0.3;
            transition: 0.3s;
        }
        .album-card:hover audio { opacity: 1; }

        @media (max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <aside class="sidebar">
        <a href="#" class="logo">STUDIO<span>PRO</span></a>
        
        <nav class="nav flex-column gap-2">
            <a class="nav-link text-white fw-bold p-0 mb-3" href="index.php"><i class="bi bi-house-door me-2"></i> Home</a>
            <a class="nav-link text-white-50 p-0 mb-3" href="add_album.php"><i class="bi bi-plus-circle me-2"></i> Add New Album</a>
            <a class="nav-link text-white-50 p-0 mb-3" href="#"><i class="bi bi-collection-play me-2"></i> Your Library</a>
            <a class="nav-link text-white-50 p-0" href="#"><i class="bi bi-heart me-2"></i> Liked Songs</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div class="search-container w-50">
                <i class="bi bi-search"></i>
                <input type="text" id="studioSearch" placeholder="Search by Artist, Title or Genre...">
            </div>
            <div>
                <span class="badge bg-dark border border-secondary p-2 px-3">2026 Edition</span>
            </div>
        </div>

        <h2 class="fw-bold mb-4">Discover <span class="text-danger">Albums</span></h2>

        

        <div class="album-grid" id="albumGrid">
            <?php while($row = mysqli_fetch_assoc($albums)): 
                $id = $row['id'];
                $coverPath = "../admin/uploads/albums/" . $row['cover'];
                $audioPath = "../admin/uploads/albums/" . $row['audio'];
                $videoPath = "../admin/uploads/albums/" . $row['video'];
            ?>
            <div class="album-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']) ?>">
                <div class="admin-tools">
                    <a href="edit_album.php?id=<?= $id ?>" class="tool-btn"><i class="bi bi-pencil-square"></i></a>
                    <a href="?delete=<?= $id ?>" class="tool-btn" onclick="return confirm('Delete permanently?')"><i class="bi bi-trash"></i></a>
                </div>

                <div class="media-box">
                    <?php if(!empty($row['video'])): ?>
                        <video id="vid-<?= $id ?>" preload="metadata" loop muted poster="<?= $coverPath ?>">
                            <source src="<?= $videoPath ?>" type="video/mp4">
                        </video>
                    <?php else: ?>
                        <img src="<?= $coverPath ?>" alt="Cover">
                    <?php endif; ?>
                    
                    <button class="play-trigger" onclick="toggleStudioPlay('<?= $id ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>

                <div class="album-info">
                    <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                    <div class="artist"><?= htmlspecialchars($row['artist']) ?></div>
                    
                    <audio id="aud-<?= $id ?>" onplay="syncMedia(this, '<?= $id ?>')">
                        <source src="<?= $audioPath ?>" type="audio/mpeg">
                    </audio>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>

<script>
    // Immersive Playback Control
    function toggleStudioPlay(id, btn) {
        const audio = document.getElementById('aud-' + id);
        const video = document.getElementById('vid-' + id);
        const icon = btn.querySelector('i');

        if (audio.paused) {
            // Pause all other media
            document.querySelectorAll('audio, video').forEach(m => m.pause());
            document.querySelectorAll('.play-trigger i').forEach(i => i.className = 'bi bi-play-fill');

            audio.play();
            if (video) {
                video.currentTime = audio.currentTime;
                video.play();
            }
            icon.className = 'bi bi-pause-fill';
        } else {
            audio.pause();
            if (video) video.pause();
            icon.className = 'bi bi-play-fill';
        }
    }

    // Sync Video with Audio
    function syncMedia(audioElement, id) {
        const video = document.getElementById('vid-' + id);
        if (video) {
            video.currentTime = audioElement.currentTime;
        }
    }

    // High Performance Search
    document.getElementById('studioSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.album-card').forEach(card => {
            card.style.display = card.dataset.search.includes(term) ? 'block' : 'none';
        });
    });
</script>

</body>
</html>