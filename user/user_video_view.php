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
    <style>:root {
    --bg: #0d0d0d;                    /* Darker background */
    --card: #1b1b1b;                  /* Slightly lighter cards */
    --accent: #ff3366;                /* Strong accent color */
    --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
    --text-main: #f5f5f5;             /* Softer white for text */
    --text-muted: #999;               /* Muted text */
    --shadow: rgba(0,0,0,0.6);        /* Card shadow */
}

/* --- Body & Wrapper --- */
body {
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
    margin: 0;
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
    transition: all 0.3s;
}
.search-box:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 8px var(--accent);
}
.btn-back {
    background: #222;
    border: none;
    color: var(--text-main);
    padding: 7px 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: 0.3s;
}
.btn-back:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Video Grid --- */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 25px;
}
.video-card {
    background: var(--card);
    border-radius: 20px;
    overflow: hidden;
    padding: 12px;
    position: relative;
    border: 1px solid #2a2a2a;
    box-shadow: 0 4px 15px var(--shadow);
    transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
}
.video-card:hover {
    transform: translateY(-6px);
    border-color: var(--accent);
    box-shadow: 0 8px 20px var(--shadow);
}

/* --- Media Wrapper --- */
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 12px;
}
.media-wrapper img,
.media-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
    transition: transform 0.5s ease;
}
.video-card:hover .media-wrapper img,
.video-card:hover .media-wrapper video {
    transform: scale(1.07);
}

/* --- Play Button --- */
.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 45px;
    height: 45px;
    background: var(--accent-grad);
    border-radius: 50%;
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    cursor: pointer;
    z-index: 5;
    transition: 0.3s;
}
.video-card:hover .play-btn {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
}

/* --- Custom Controls --- */
.custom-controls {
    position: absolute;
    bottom: 6px;
    left: 6px;
    right: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 12px;
    background: rgba(30, 30, 30, 0.75);
    backdrop-filter: blur(6px);
    opacity: 0;
    border-radius: 0 0 12px 12px;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.media-wrapper:hover .custom-controls {
    opacity: 1;
}
.custom-controls button {
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 1.2rem;
    transition: 0.25s;
}
.custom-controls button:hover {
    color: var(--accent);
    transform: scale(1.25);
}
.custom-controls input[type="range"] {
    flex: 1;
    margin: 0 6px;
    accent-color: var(--accent);
    background: rgba(255, 255, 255, 0.12);
    border-radius: 4px;
}
.custom-controls input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--accent);
    border: 2px solid #1b1b1b;
    transition: transform 0.2s;
}
.custom-controls input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.3);
}

/* --- Titles & Stars --- */
.title {
    font-size: 0.88rem;
    font-weight: 600;
    margin: 6px 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.stars-display {
    font-size: 0.78rem;
    color: #ffd700;
    margin-bottom: 8px;
}

/* --- Review Button --- */
.rev-btn {
    background: #2b2b2b;
    color: #fff;
    border: none;
    font-size: 0.75rem;
    width: 100%;
    padding: 7px;
    border-radius: 8px;
    transition: 0.3s;
}
.rev-btn:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Review Overlay --- */
#reviewOverlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.review-box {
    background: var(--card);
    width: 90%;
    max-width: 420px;
    padding: 35px;
    border-radius: 22px;
    border: 1px solid #2a2a2a;
}

/* --- Star Rating --- */
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 10px;
    margin-bottom: 18px;
}
.star-rating label {
    font-size: 2.2rem;
    color: #333;
    cursor: pointer;
    transition: 0.3s;
}
.star-rating label:hover,
.star-rating label:hover~label,
.star-rating input:checked~label {
    color: #ffd700;
}

/* --- Footer --- */
footer {
    text-align: center;
    padding: 50px 0;
    font-size: 0.75rem;
    color: #555;
}

    </style>

</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search videos...">
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="videoGrid">
            <?php while ($row = mysqli_fetch_assoc($videos)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="video-card" data-title="<?= strtolower($row['title']); ?>">
                    <div class="media-wrapper">
                        <video id="vid-<?= $row['id'] ?>" loop muted playsinline>
                            <source src="../admin/uploads/videos/<?= $row['file'] ?>" type="video/mp4">
                        </video>
                        <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                        <div class="custom-controls">
                            <input type="range" class="progress" min="0" max="100" value="0">
                            <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                            <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
                        </div>
                        <button class="play-btn" onclick="toggleVideo('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>
                    <p class="title"><?= htmlspecialchars($row['title']) ?></p>
                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="text-white opacity-50 ms-1">(<?= $row['total_reviews'] ?>)</span>
                    </div>
                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        <i class="bi bi-chat-dots-fill me-1"></i> REVIEW
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-box">
            <div class="text-center mb-4">
                <h5 class="fw-bold m-0" id="revTitle">Video Name</h5>
                <small class="text-muted">How was your visual experience?</small>
            </div>

            <form method="POST">
                <input type="hidden" name="video_id" id="revVideoId">

                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="v5" required><label for="v5">★</label>
                    <input type="radio" name="rating" value="4" id="v4"><label for="v4">★</label>
                    <input type="radio" name="rating" value="3" id="v3"><label for="v3">★</label>
                    <input type="radio" name="rating" value="2" id="v2"><label for="v2">★</label>
                    <input type="radio" name="rating" value="1" id="v1"><label for="v1">★</label>
                </div>

                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="4" placeholder="Write your review here..." required></textarea>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark w-100" onclick="closeReview()">CANCEL</button>
                    <button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color:white;">POST NOW</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 VIDEO STUDIO &bull; SYSTEM PRO</footer>

    <script>
        // --- Search Functionality ---
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".video-card").forEach(card => {
                card.style.display = card.dataset.title.includes(val) ? "block" : "none";
            });
        });

        // --- Toggle Video Play/Pause ---
        function toggleVideo(id, btn) {
            const video = document.getElementById('vid-' + id);
            const icon = btn.querySelector('i');

            // Pause all other videos
            document.querySelectorAll('video').forEach(v => {
                if (v !== video) v.pause();
            });

            if (video.paused) {
                video.play();
                icon.className = 'bi bi-pause-fill';
            } else {
                video.pause();
                icon.className = 'bi bi-play-fill';
            }
        }

        // --- Custom Controls ---
        document.querySelectorAll('.media-wrapper').forEach(wrapper => {
            const video = wrapper.querySelector('video');
            if (!video) return;

            const progress = wrapper.querySelector('.progress');
            const muteBtn = wrapper.querySelector('.mute-btn');
            const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');

            // Update progress
            video.addEventListener('timeupdate', () => {
                if (progress) progress.value = (video.currentTime / video.duration) * 100;
            });

            // Seek video
            if (progress) progress.addEventListener('input', () => {
                video.currentTime = (progress.value / 100) * video.duration;
            });

            // Mute toggle
            if (muteBtn) muteBtn.addEventListener('click', e => {
                e.stopPropagation();
                video.muted = !video.muted;
                muteBtn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
            });

            // Fullscreen
            if (fullscreenBtn) fullscreenBtn.addEventListener('click', e => {
                e.stopPropagation();
                if (video.requestFullscreen) video.requestFullscreen();
            });
        });

        // --- Review Modal ---
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