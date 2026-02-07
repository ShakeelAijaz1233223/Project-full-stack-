<?php
include "../config/db.php";

// Fetch Music/Videos with Ratings
$query = "SELECT music.*, 
          (SELECT AVG(rating) FROM reviews WHERE reviews.music_id = music.id) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE reviews.music_id = music.id) as total_reviews
          FROM music WHERE video IS NOT NULL AND video != '' ORDER BY id DESC";
$music = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #080808;
            --card-bg: #121212;
            --accent: #ff3366;
            --accent-glow: rgba(255, 51, 102, 0.4);
            --text-main: #ffffff;
            --text-dim: #b3b3b3;
            --glass: rgba(255, 255, 255, 0.03);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.02em;
        }

        .studio-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* --- Header Navigation --- */
        .glass-nav {
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 20px;
            z-index: 1000;
        }

        .search-wrapper {
            position: relative;
            width: 350px;
        }

        .search-box {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 50px;
            padding: 10px 20px 10px 45px;
            color: white;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
        }

        /* --- Video Grid --- */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .video-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 15px;
            border: 1px solid #222;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .video-card:hover {
            transform: translateY(-10px);
            border-color: #444;
            background: #181818;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        /* --- Video Player Section --- */
        .video-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: 18px;
            overflow: hidden;
            background: #000;
            margin-bottom: 20px;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .video-overlay-btn {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
            cursor: pointer;
            border: none;
        }

        .video-card:hover .video-overlay-btn {
            opacity: 1;
        }

        .play-icon {
            font-size: 3rem;
            color: white;
            filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));
        }

        /* --- Info Section --- */
        .track-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist-name {
            color: var(--accent);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 15px;
            display: block;
        }

        .meta-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 20px;
        }

        .badge-item {
            background: var(--glass);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            color: var(--text-dim);
            border: 1px solid rgba(255,255,255,0.05);
        }

        /* --- Action Buttons --- */
        .btn-action {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-download {
            background: var(--accent);
            color: white;
            box-shadow: 0 5px 15px var(--accent-glow);
        }

        .btn-download:hover {
            background: #e62e5c;
            color: white;
            transform: scale(1.02);
        }

        .btn-back-main {
            background: var(--glass);
            color: white;
            border: 1px solid #333;
        }

        .btn-back-main:hover {
            background: #222;
            color: white;
        }

        footer {
            text-align: center;
            padding: 50px 0;
            border-top: 1px solid #222;
            margin-top: 50px;
            color: var(--text-dim);
            font-size: 0.8rem;
        }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            .glass-nav { flex-direction: column; gap: 15px; }
            .search-wrapper { width: 100%; }
        }
    </style>
</head>
<body>

<div class="studio-container">
    <header class="glass-nav">
        <h3 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h3>
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="search" class="search-box" placeholder="Search music videos...">
        </div>
        <a href="javascript:history.back()" class="btn btn-outline-light btn-sm rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Back to Audio
        </a>
    </header>

    <div class="video-grid" id="videoGrid">
        <?php while ($row = mysqli_fetch_assoc($music)): 
            $avg = round($row['avg_rating'], 1);
        ?>
            <div class="video-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                <div class="video-wrapper">
                    <video id="video-<?= $row['id'] ?>" preload="metadata" poster="../admin/uploads/music_covers/<?= $row['cover_image'] ?? 'default.jpg' ?>">
                        <source src="../admin/uploads/music/<?= $row['video'] ?>" type="video/mp4">
                    </video>
                    <button class="video-overlay-btn" onclick="toggleVideo('<?= $row['id'] ?>')">
                        <i class="bi bi-play-circle-fill play-icon"></i>
                    </button>
                </div>

                <h5 class="track-title"><?= htmlspecialchars($row['title']) ?></h5>
                <span class="artist-name"><?= htmlspecialchars($row['artist']) ?></span>

                <div class="meta-badges">
                    <span class="badge-item"><i class="bi bi-disc me-1"></i> <?= htmlspecialchars($row['album']) ?></span>
                    <span class="badge-item"><i class="bi bi-calendar-check me-1"></i> <?= $row['year'] ?></span>
                    <span class="badge-item text-warning">
                        <i class="bi bi-star-fill me-1"></i> <?= $avg ?> (<?= $row['total_reviews'] ?>)
                    </span>
                </div>

                <a href="../admin/uploads/music/<?= $row['video'] ?>" download class="btn-action btn-download">
                    <i class="bi bi-cloud-arrow-down-fill"></i> DOWNLOAD MP4
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<footer>
    <p>&copy; 2026 Studio Pro Visuals &bull; High Definition Experience</p>
</footer>

<script>
    // Video Play/Pause Logic
    function toggleVideo(id) {
        const video = document.getElementById('video-' + id);
        const btn = video.nextElementSibling.querySelector('i');

        // Pause other videos when one starts
        document.querySelectorAll('video').forEach(v => {
            if (v !== video) {
                v.pause();
                v.nextElementSibling.querySelector('i').className = 'bi bi-play-circle-fill play-icon';
            }
        });

        if (video.paused) {
            video.play();
            video.controls = true; // Show native controls once playing
            btn.className = 'bi bi-pause-circle-fill play-icon';
            video.nextElementSibling.style.opacity = '0'; // Hide overlay
        } else {
            video.pause();
            btn.className = 'bi bi-play-circle-fill play-icon';
            video.nextElementSibling.style.opacity = '1';
        }
    }

    // Video Overlay Visibility on Hover
    document.querySelectorAll('.video-wrapper').forEach(wrapper => {
        const video = wrapper.querySelector('video');
        const overlay = wrapper.querySelector('.video-overlay-btn');

        video.onplay = () => { overlay.style.opacity = '0'; };
        video.onpause = () => { overlay.style.opacity = '1'; };
    });

    // Search Logic
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".video-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });
</script>

</body>
</html>