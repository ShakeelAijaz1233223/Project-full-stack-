<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $video_id = mysqli_real_escape_string($conn, $_POST['video_id']);
    $rating = (int)$_POST['rating'];
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
          :root {
            --bg: #0d0d0d;
            --card: #1b1b1b;
            --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #f5f5f5;
            --text-muted: #999;
            --shadow: rgba(0, 0, 0, 0.8);
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

        /* --- Header --- */
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

        .btn-back {
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

        .btn-back:hover {
            background: var(--accent);
            color: #fff;
        }

        /* --- Grid & Cards --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .music-card {
            background: var(--card);
            border-radius: 20px;
            padding: 12px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 10px 20px var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .music-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent);
        }

        /* --- Media Wrapper (Vinyl Animation) --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: linear-gradient(45deg, #111, #222);
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .vinyl-disc {
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle, #333 20%, #111 21%, #111 100%);
            border: 2px solid #222;
            animation: rotate 5s linear infinite;
            animation-play-state: paused;
        }

        .music-card.playing .vinyl-disc {
            animation-play-state: running;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

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

        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 11;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .progress {
            flex: 1;
            height: 5px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        /* --- Text Styling --- */
        .title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 8px 0 12px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .meta-info span {
            background: rgba(255, 255, 255, 0.05);
            padding: 3px 6px;
            border-radius: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: 0.3s;
        }

        .meta-info span:hover {
            background: rgba(255, 51, 102, 0.15);
            color: var(--text-main);
        }

        .artist-tag {
            color: var(--accent) !important;
            font-weight: 600;
            background: rgba(255, 51, 102, 0.1) !important;
        }

        .stars-display {
            color: #ffd700;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .rev-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 8px;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 7px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.75rem;
            transition: 0.3s;
        }

        .download-btn:hover {
            background: #333;
            color: #fff;
        }

        /* --- Review Modal --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-box {
            background: #151515;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #333;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 5px;
            margin-bottom: 20px;
        }

        .star-rating label {
            font-size: 2.5rem;
            color: #333;
            cursor: pointer;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #ffd700;
        }

        footer {
            text-align: center;
            padding: 40px;
            color: #444;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">Video<span style="color: var(--accent);">Studio</span></h4>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search visuals, artists...">
            <a href="index.php" class="btn-back"><i class="bi bi-house"></i> Home</a>
        </div>
    </div>

    <div class="grid" id="videoGrid">
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
                        <input type="range" class="progress" min="0" max="100" value="0">
                        <button class="btn btn-sm text-white p-0 border-0" onclick="toggleMute('<?= $row['id'] ?>', this)">
                            <i class="bi bi-volume-up"></i>
                        </button>
                    </div>
                </div>

                <p class="title"><?= htmlspecialchars($row['title']) ?></p>
                
                <div class="meta-info">
                    <span class="artist-tag">Artist: <?= htmlspecialchars($row['artist']) ?></span>
                    <span class="album-tag">Source: <?= htmlspecialchars($row['album']) ?></span>
                    <span class="year-tag">Year: <?= htmlspecialchars($row['year']) ?></span>
                </div>

                <div class="stars-display">
                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span style="color: #666; font-size: 0.7rem;">(<?= $row['total_reviews'] ?>)</span>
                </div>

                <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                    <i class="bi bi-chat-square-text me-2"></i>ADD REVIEW
                </button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-box">
        <h5 class="text-center mb-1" id="revTitle">Video Name</h5>
        <p class="text-center text-muted small mb-4">How was the visual quality?</p>
        
        <form method="POST">
            <input type="hidden" name="video_id" id="revVideoId">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Share your feedback..." required></textarea>
            
            <div class="row g-2">
                <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeReview()">CANCEL</button></div>
                <div class="col-6"><button type="submit" name="submit_review" class="btn btn-primary w-100" style="background: var(--accent); border:none;">POST</button></div>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 Video Studio Pro &bull; Optimized Experience</footer>

<script>
    // Search
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".video-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });

    // Media Logic
    function handleMedia(id, btn) {
        const video = document.getElementById('vid-' + id);
        const thumb = document.getElementById('thumb-' + id);
        const icon = btn.querySelector('i');

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

    // Progress
    document.querySelectorAll('video').forEach(video => {
        const progress = video.closest('.media-wrapper').querySelector('.progress');
        video.addEventListener('timeupdate', () => {
            if (video.duration) progress.value = (video.currentTime / video.duration) * 100;
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

    function openReview(id, title) {
        document.getElementById('revVideoId').value = id;
        document.getElementById('revTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }

    function closeReview() { document.getElementById('reviewOverlay').style.display = 'none'; }
</script>
</body>
</html>