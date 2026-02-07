<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $video_id = $_POST['video_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn, "INSERT INTO video_reviews (video_id, rating, comment) VALUES ('$video_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch videos with average rating and metadata
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
    <title>Video Studio | Premium Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg: #0d0d0d;
            --card: #1b1b1b;
            --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #f5f5f5;
            --text-muted: #999;
            --shadow: rgba(0,0,0,0.8);
        }

        body {
            background: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 25px 0;
        }

        /* --- Header Section --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .search-box {
            background: #1f1f1f;
            border: 1px solid #333;
            color: var(--text-main);
            border-radius: 10px;
            padding: 8px 16px;
            width: 280px;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(255, 51, 102, 0.3);
        }

        .btn-nav {
            background: #222;
            border: none;
            color: var(--text-main);
            padding: 7px 18px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .btn-nav:hover {
            background: var(--accent);
            color: #fff;
        }

        /* --- Video Grid & Cards --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .video-card {
            background: var(--card);
            border-radius: 20px;
            padding: 12px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 10px 20px var(--shadow);
            transition: all 0.3s ease;
        }

        .video-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent);
        }

        /* --- Media Player Container --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0; left: 0;
            z-index: 2;
            transition: opacity 0.5s ease;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0; left: 0;
            z-index: 1;
        }

        /* --- Visual Play Button --- */
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 55px;
            height: 55px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            cursor: pointer;
            z-index: 10;
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.5);
        }

        /* --- Bottom Controls Overlay --- */
        .custom-controls {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
            padding: 15px 10px 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 11;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .progress-bar-container {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .video-progress {
            width: 100%;
            height: 4px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .control-icon {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.1rem;
            padding: 0;
            transition: 0.2s;
        }

        .control-icon:hover {
            color: var(--accent);
            transform: scale(1.1);
        }

        /* --- Content Styling --- */
        .video-title {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-tags {
            display: flex;
            gap: 6px;
            margin: 8px 0 12px;
            flex-wrap: wrap;
        }

        .tag {
            font-size: 0.7rem;
            padding: 2px 10px;
            border-radius: 6px;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            font-weight: 500;
        }

        .artist-tag {
            background: rgba(255, 51, 102, 0.1);
            color: var(--accent);
        }

        .rating-stars {
            color: #ffd700;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .action-btn {
            width: 100%;
            padding: 10px;
            border-radius: 12px;
            border: none;
            background: #252525;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.3s;
        }

        .action-btn:hover {
            background: var(--accent);
        }

        /* --- Review Modal --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(10px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-card {
            background: #151515;
            padding: 35px;
            border-radius: 25px;
            width: 90%;
            max-width: 420px;
            border: 1px solid #333;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .star-rating label { font-size: 2.8rem; color: #333; cursor: pointer; transition: 0.2s; }
        .star-rating input { display: none; }
        .star-rating label:hover, .star-rating label:hover~label, .star-rating input:checked~label { color: #ffd700; }

        footer { text-align: center; padding: 50px 0; color: #444; font-size: 0.75rem; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search visuals...">
            <a href="index.php" class="btn-nav"><i class="bi bi-house-door"></i> Dashboard</a>
        </div>
    </div>

    <div class="grid" id="videoGrid">
        <?php while ($row = mysqli_fetch_assoc($videos)): 
            $avg = round($row['avg_rating'], 1);
            $thumbPath = !empty($row['thumbnail']) ? "../admin/uploads/video_thumbnails/".$row['thumbnail'] : "../assets/img/default_thumb.jpg";
        ?>
            <div class="video-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                <div class="media-wrapper">
                    <img src="<?= $thumbPath ?>" class="thumb-img" id="thumb-<?= $row['id'] ?>">
                    
                    <video id="vid-<?= $row['id'] ?>" loop playsinline preload="none">
                        <source src="../admin/uploads/videos/<?= $row['file'] ?>" type="video/mp4">
                    </video>

                    <button class="play-btn" onclick="togglePlayback('<?= $row['id'] ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>

                    <div class="custom-controls">
                        <div class="progress-bar-container">
                            <input type="range" class="video-progress" min="0" max="100" value="0">
                        </div>
                        <button class="control-icon" onclick="toggleMute('<?= $row['id'] ?>', this)">
                            <i class="bi bi-volume-up"></i>
                        </button>
                        <button class="control-icon" onclick="goFullscreen('<?= $row['id'] ?>')">
                            <i class="bi bi-fullscreen"></i>
                        </button>
                    </div>
                </div>

                <p class="video-title"><?= htmlspecialchars($row['title']) ?></p>
                
                <div class="meta-tags">
                    <span class="tag artist-tag"><?= htmlspecialchars($row['artist']) ?></span>
                    <span class="tag"><?= htmlspecialchars($row['album']) ?></span>
                    <span class="tag"><?= htmlspecialchars($row['year']) ?></span>
                </div>

                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span style="color: #555; font-size: 0.7rem; margin-left: 5px;">(<?= $row['total_reviews'] ?> reviews)</span>
                </div>

                <button class="action-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                    <i class="bi bi-star-half me-2"></i>RATE EXPERIENCE
                </button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-card">
        <h5 class="text-center fw-bold mb-1" id="revTitle">Video Title</h5>
        <p class="text-center text-muted small mb-0">Rate the cinematography & sound</p>
        
        <form method="POST">
            <input type="hidden" name="video_id" id="revVideoId">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-4" rows="3" placeholder="What did you think of this visual?" required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-dark flex-grow-1" style="border-radius:12px;" onclick="closeReview()">DISMISS</button>
                <button type="submit" name="submit_review" class="btn flex-grow-1" style="background: var(--accent); color:white; border-radius:12px; font-weight:600;">SUBMIT REVIEW</button>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 VIDEO STUDIO PRO &bull; MOTION GRAPHICS UNIT</footer>

<script>
    // 1. Live Search
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".video-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });

    // 2. Playback Engine
    function togglePlayback(id, btn) {
        const video = document.getElementById('vid-' + id);
        const thumb = document.getElementById('thumb-' + id);
        const icon = btn.querySelector('i');

        // Stop all other playing videos
        document.querySelectorAll('video').forEach(v => {
            if (v !== video && !v.paused) {
                v.pause();
                const otherWrapper = v.closest('.media-wrapper');
                otherWrapper.querySelector('.thumb-img').style.opacity = '1';
                otherWrapper.querySelector('.play-btn i').className = 'bi bi-play-fill';
            }
        });

        if (video.paused) {
            video.play();
            thumb.style.opacity = '0'; // Hide thumbnail
            icon.className = 'bi bi-pause-fill';
        } else {
            video.pause();
            thumb.style.opacity = '1'; // Show thumbnail
            icon.className = 'bi bi-play-fill';
        }
    }

    // 3. Progress Bar Sync
    document.querySelectorAll('video').forEach(video => {
        const wrapper = video.closest('.media-wrapper');
        const progress = wrapper.querySelector('.video-progress');
        
        video.addEventListener('timeupdate', () => {
            if(video.duration) {
                progress.value = (video.currentTime / video.duration) * 100;
            }
        });

        progress.addEventListener('input', () => {
            video.currentTime = (progress.value / 100) * video.duration;
        });
    });

    // 4. Sound & Screen Controls
    function toggleMute(id, btn) {
        const video = document.getElementById('vid-' + id);
        video.muted = !video.muted;
        btn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
    }

    function goFullscreen(id) {
        const video = document.getElementById('vid-' + id);
        if (video.requestFullscreen) {
            video.requestFullscreen();
        } else if (video.webkitRequestFullscreen) { /* Safari */
            video.webkitRequestFullscreen();
        }
    }

    // 5. Review Modal Management
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