<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $video_id = $_POST['video_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn, "INSERT INTO video_reviews (video_id, rating, comment) VALUES ('$video_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// Fetch videos with average rating
$query = "SELECT videos.*, 
          (SELECT AVG(rating) FROM video_reviews WHERE video_reviews.video_id = videos.id) as avg_rating,
          (SELECT COUNT(*) FROM video_reviews WHERE video_reviews.video_id = videos.id) as total_reviews
          FROM videos ORDER BY id DESC";
$videos = mysqli_query($conn, $query);
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
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.02em;
        }

        .studio-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* --- Header Section (Glassmorphism) --- */
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

        /* --- Grid & Modern Cards --- */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .video-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 18px;
            border: 1px solid #222;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .video-card:hover {
            transform: translateY(-10px);
            border-color: #444;
            background: #181818;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        /* --- Media Wrapper (16:9) --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            z-index: 2;
            transition: opacity 0.5s ease;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* --- Play Button Overlay --- */
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 65px;
            height: 65px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            cursor: pointer;
            z-index: 10;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(255, 51, 102, 0.4);
        }

        .video-card:hover .play-btn {
            transform: translate(-50%, -50%) scale(1.1);
        }

        .custom-controls {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 11;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls { opacity: 1; }

        .progress-bar-custom {
            flex: 1;
            height: 4px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        /* --- Info Section & Badges --- */
        .track-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist-name {
            color: var(--accent);
            font-size: 0.95rem;
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
            font-size: 0.75rem;
            color: var(--text-dim);
            border: 1px solid rgba(255,255,255,0.05);
        }

        /* --- Buttons --- */
        .btn-action {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: 0.3s;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-review {
            background: #fff;
            color: #000;
        }

        .btn-review:hover { background: #e0e0e0; }

        /* --- Review Modal Overlay --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.95);
            backdrop-filter: blur(10px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .review-card {
            background: #111;
            padding: 40px;
            border-radius: 30px;
            width: 90%;
            max-width: 450px;
            border: 1px solid #222;
            text-align: center;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .star-rating input { display: none; }
        .star-rating label { font-size: 2.5rem; color: #222; cursor: pointer; transition: 0.2s; }
        .star-rating label:hover, 
        .star-rating label:hover ~ label, 
        .star-rating input:checked ~ label { color: #ffd700; }

        footer { text-align: center; padding: 50px 0; color: #444; border-top: 1px solid #222; margin-top: 50px; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <header class="glass-nav">
        <h3 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h3>
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text" id="search" class="search-box" placeholder="Search visuals, artists...">
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-4">
            <i class="bi bi-house-door me-2"></i>Home
        </a>
    </header>

    <div class="video-grid" id="videoGrid">
        <?php while ($row = mysqli_fetch_assoc($videos)): 
            $avg = round($row['avg_rating'], 1);
            $thumbnail = !empty($row['thumbnail']) ? "../admin/uploads/video_thumbnails/".$row['thumbnail'] : "../assets/img/default_thumb.jpg";
        ?>
            <div class="video-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                <div class="media-wrapper">
                    <img src="<?= $thumbnail ?>" class="thumb-img" id="thumb-<?= $row['id'] ?>">
                    
                    <video id="vid-<?= $row['id'] ?>" loop playsinline>
                        <source src="../admin/uploads/videos/<?= $row['file'] ?>" type="video/mp4">
                    </video>

                    <button class="play-btn" onclick="handleMedia('<?= $row['id'] ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>

                    <div class="custom-controls">
                        <input type="range" class="progress-bar-custom progress" min="0" max="100" value="0">
                        <button class="btn btn-sm text-white" onclick="toggleMute('<?= $row['id'] ?>', this)"><i class="bi bi-volume-up"></i></button>
                        <button class="btn btn-sm text-white" onclick="toggleFS('<?= $row['id'] ?>')"><i class="bi bi-arrows-fullscreen"></i></button>
                    </div>
                </div>

                <h5 class="track-title"><?= htmlspecialchars($row['title']) ?></h5>
                <span class="artist-name"><?= htmlspecialchars($row['artist']) ?></span>
                
                <div class="meta-badges">
                    <span class="badge-item"><i class="bi bi-disc me-1"></i> <?= htmlspecialchars($row['album']) ?></span>
                    <span class="badge-item"><i class="bi bi-calendar3 me-1"></i> <?= htmlspecialchars($row['year']) ?></span>
                    <span class="badge-item text-warning">
                        <i class="bi bi-star-fill me-1"></i> <?= $avg ?> (<?= $row['total_reviews'] ?>)
                    </span>
                </div>

                <button class="btn-action btn-review" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                    <i class="bi bi-chat-dots"></i> ADD A REVIEW
                </button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-card">
        <h3 id="revTitle" class="mb-1">Video Name</h3>
        <p class="text-dim small">How was the visual quality?</p>
        
        <form method="POST">
            <input type="hidden" name="video_id" id="revVideoId">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-4" rows="4" placeholder="Share your feedback..." required></textarea>
            
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" onclick="closeReview()">CANCEL</button>
                <button type="submit" name="submit_review" class="btn btn-primary w-100 rounded-pill" style="background: var(--accent); border:none;">POST</button>
            </div>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2026 Studio Pro Visuals &bull; High Definition Experience</p>
</footer>

<script>
    // Search Filter
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".video-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });

    // Play/Pause + Thumbnail Toggle
    function handleMedia(id, btn) {
        const video = document.getElementById('vid-' + id);
        const thumb = document.getElementById('thumb-' + id);
        const icon = btn.querySelector('i');

        // Stop other videos
        document.querySelectorAll('video').forEach(v => {
            if (v !== video) {
                v.pause();
                const vThumb = v.closest('.media-wrapper').querySelector('.thumb-img');
                if(vThumb) vThumb.style.opacity = '1';
                v.closest('.media-wrapper').querySelector('.play-btn i').className = 'bi bi-play-fill';
            }
        });

        if (video.paused) {
            video.play();
            thumb.style.opacity = '0';
            icon.className = 'bi bi-pause-fill';
        } else {
            video.pause();
            thumb.style.opacity = '1';
            icon.className = 'bi bi-play-fill';
        }
    }

    // Progress Bar
    document.querySelectorAll('video').forEach(video => {
        const wrapper = video.closest('.media-wrapper');
        const progress = wrapper.querySelector('.progress');
        
        video.addEventListener('timeupdate', () => {
            if (video.duration) {
                progress.value = (video.currentTime / video.duration) * 100;
            }
        });

        progress.addEventListener('input', () => {
            video.currentTime = (progress.value / 100) * video.duration;
        });
    });

    function toggleMute(id, btn) {
        const video = document.getElementById('vid-' + id);
        video.muted = !video.muted;
        btn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
    }

    function toggleFS(id) {
        const video = document.getElementById('vid-' + id);
        if (video.requestFullscreen) video.requestFullscreen();
        else if (video.webkitRequestFullscreen) video.webkitRequestFullscreen();
        else if (video.msRequestFullscreen) video.msRequestFullscreen();
    }

    function openReview(id, title) {
        document.getElementById('revVideoId').value = id;
        document.getElementById('revTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }

    function closeReview() {
        document.getElementById('reviewOverlay').style.display = 'none';
    }
</script>

</body>
</html>