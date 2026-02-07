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
    <style>
      :root {
    --primary-gradient: linear-gradient(135deg, #ff0055, #7000ff);
    --bg-dark: #080808;
    --card-bg: rgba(255, 255, 255, 0.03);
    --glass-border: rgba(255, 255, 255, 0.1);
    --text-main: #ffffff;
    --text-dim: #a0a0a0;
    --accent: #ff0055;
}

body {
    background-color: var(--bg-dark);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
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
    border-radius: 8px;
    padding: 8px 15px;
    width: 250px;
    font-size: 0.85rem;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    padding: 20px 0;
}

.video-card {
    background: var(--card-bg);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 25px;
    padding: 20px;
    transition: transform 0.5s ease, box-shadow 0.5s ease, border-color 0.5s ease, background 0.5s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.video-card:hover {
    transform: translateY(-15px) scale(1.05);
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 0, 85, 0.6);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
}

.media-wrapper {
    border-radius: 15px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #000;
    margin-bottom: 15px;
}

.media-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.video-card:hover .media-wrapper video {
    transform: scale(1.15);
}

.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 0, 85, 0.3);
    border: none;
    padding: 16px 20px;
    border-radius: 50%;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    transition: transform 0.3s ease, background 0.3s ease;
    z-index: 5;
}

.play-btn:hover {
    background: rgba(255, 0, 85, 0.6);
    transform: translate(-50%, -50%) scale(1.1);
}

.title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: 0.5px;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stars-display {
    color: #ffca08;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 3px;
    margin-bottom: 15px;
}

.rev-btn {
    background: var(--primary-gradient);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 10px;
    width: 100%;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
}

.rev-btn:hover {
    box-shadow: 0 6px 20px rgba(255, 0, 85, 0.5);
    filter: brightness(1.2);
}

#reviewOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.review-box {
    background: #111;
    width: 90%;
    max-width: 400px;
    padding: 30px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.star-rating input {
    display: none;
}

.star-rating label {
    font-size: 2.5rem;
    color: #222;
    cursor: pointer;
}

.star-rating label:hover,
.star-rating label:hover~label,
.star-rating input:checked~label {
    color: #ffca08;
}

footer {
    padding: 40px;
    text-align: center;
    font-size: 0.7rem;
    color: #444;
}

    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search videos...">
            </div>
            <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i>Back</a>            
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
      // ------------------------
// Search Filter
// ------------------------
document.getElementById("search").addEventListener("input", function() {
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".video-card").forEach(card => {
        let text = card.dataset.title;
        card.style.display = text.includes(val) ? "block" : "none";
    });
});

// ------------------------
// Video Player Controls
// ------------------------
document.querySelectorAll('.media-wrapper').forEach(wrapper => {
    const video = wrapper.querySelector('video');
    if (!video) return;

    // Create custom controls
    let controls = document.createElement('div');
    controls.className = 'custom-controls';
    controls.innerHTML = `
        <input type="range" class="progress" min="0" max="100" value="0">
        <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
        <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
    `;
    wrapper.appendChild(controls);

    const playBtn = wrapper.querySelector('.play-btn');
    const progress = wrapper.querySelector('.progress');
    const muteBtn = wrapper.querySelector('.mute-btn');
    const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');

    // Play/Pause
    playBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        document.querySelectorAll('video').forEach(v => {
            if (v !== video) {
                v.pause();
               
                let btn = v.closest('.media-wrapper').querySelector('.play-btn i');
                if(btn) btn.className = 'bi bi-play-fill';
            }
        });

        if (video.paused) {
            video.play();
            playBtn.querySelector('i').className = 'bi bi-pause-fill';
        } else {
            video.pause();
            playBtn.querySelector('i').className = 'bi bi-play-fill';
        }
    });

    // Update progress bar
    video.addEventListener('timeupdate', () => {
        progress.value = (video.currentTime / video.duration) * 100 || 0;
    });

    // Seek video
    progress.addEventListener('input', () => {
        video.currentTime = (progress.value / 100) * video.duration;
    });

    // Mute toggle
    muteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        video.muted = !video.muted;
        muteBtn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
    });

    // Fullscreen
    fullscreenBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (video.requestFullscreen) video.requestFullscreen();
    });

    // Show controls on hover
    wrapper.addEventListener('mouseenter', () => controls.style.opacity = 1);
    wrapper.addEventListener('mouseleave', () => controls.style.opacity = 0);
});

// ------------------------
// Review Modal Logic
// ------------------------
function openReview(id, title) {
    document.getElementById('revVideoId').value = id;
    document.getElementById('revTitle').innerText = title;
    document.getElementById('reviewOverlay').style.display = 'flex';
}

function closeReview() {
    document.getElementById('reviewOverlay').style.display = 'none';
}

// ------------------------
// Optional: click outside to close review
// ------------------------
document.getElementById('reviewOverlay').addEventListener('click', (e) => {
    if (e.target.id === 'reviewOverlay') closeReview();
});

    </script>
</body>

</html>