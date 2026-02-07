<?php
session_start();
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $album_id = mysqli_real_escape_string($conn, $_POST['album_id']);
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    $review_query = "INSERT INTO album_reviews (album_id, rating, comment) VALUES ('$album_id', '$rating', '$comment')";

    if (mysqli_query($conn, $review_query)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
        exit();
    }
}

// Fetch albums with average rating
$query = "SELECT albums.*, 
          (SELECT AVG(rating) FROM album_reviews WHERE album_reviews.album_id = albums.id) as avg_rating,
          (SELECT COUNT(*) FROM album_reviews WHERE album_reviews.album_id = albums.id) as total_reviews
          FROM albums ORDER BY created_at DESC";
$albums = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album Studio | Pro Dashboard</title>
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

        /* --- Premium Sticky Header --- */
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
            position: absolute; left: 18px; top: 50%; transform: translateY(-50%);
            color: var(--text-dim);
        }

        /* --- Album Grid & Modern Cards --- */
        .album-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .album-card {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 18px;
            border: 1px solid #222;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .album-card:hover {
            transform: translateY(-10px);
            border-color: #444;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        /* --- Media Wrapper (1:1 Ratio) --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #000;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .media-wrapper img, .media-wrapper video {
            width: 100%; height: 100%; object-fit: cover;
        }

        .play-btn {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 60px; height: 60px; background: var(--accent-grad);
            border-radius: 50%; border: none; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; cursor: pointer; z-index: 10;
            opacity: 0; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(255, 51, 102, 0.4);
        }

        .album-card:hover .play-btn { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }

        .custom-controls {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 12px; z-index: 11; opacity: 0; transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls { opacity: 1; }

        .progress-bar-custom {
            width: 100%; height: 4px; accent-color: var(--accent); cursor: pointer;
        }

        /* --- Info & Badges --- */
        .title {
            font-size: 1.1rem; font-weight: 700; margin-bottom: 5px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .meta-info {
            display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 15px;
        }

        .badge-item {
            background: var(--glass); padding: 4px 10px; border-radius: 6px;
            font-size: 0.75rem; color: var(--text-dim); border: 1px solid rgba(255,255,255,0.05);
        }

        .artist-badge { color: var(--accent); font-weight: 600; background: rgba(255, 51, 102, 0.1); }

        .stars-display { color: #ffd700; font-size: 0.85rem; margin-bottom: 15px; }

        /* --- Buttons --- */
        .rev-btn {
            width: 100%; padding: 12px; border-radius: 12px; border: none;
            background: #fff; color: #000; font-weight: 700; font-size: 0.8rem;
            transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px;
        }

        .rev-btn:hover { background: var(--accent); color: #fff; transform: scale(1.02); }

        /* --- Review Modal --- */
        #reviewOverlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.95); backdrop-filter: blur(10px);
            z-index: 2000; align-items: center; justify-content: center;
        }

        .review-card {
            background: #111; padding: 40px; border-radius: 30px;
            width: 90%; max-width: 450px; border: 1px solid #222; text-align: center;
        }

        .star-rating {
            display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin: 20px 0;
        }

        .star-rating input { display: none; }
        .star-rating label { font-size: 2.5rem; color: #222; cursor: pointer; transition: 0.2s; }
        .star-rating label:hover, .star-rating label:hover ~ label, .star-rating input:checked ~ label { color: #ffd700; }

        footer { text-align: center; padding: 50px 0; color: #444; border-top: 1px solid #222; margin-top: 50px; }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <header class="glass-nav">
            <h3 class="m-0 fw-bold">ALBUM<span style="color: var(--accent);">STUDIO</span></h3>
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="search" class="search-box" placeholder="Search albums, artists, years...">
            </div>
            <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-4">
                <i class="bi bi-house-door me-2"></i>Home
            </a>
        </header>

        <div class="album-grid" id="albumGrid">
            <?php while ($row = mysqli_fetch_assoc($albums)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="album-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                    <div class="media-wrapper">
                        <?php if (!empty($row['video'])): ?>
                            <video id="vid-<?= $row['id'] ?>" loop playsinline poster="../admin/uploads/albums/<?= $row['cover'] ?>">
                                <source src="../admin/uploads/albums/<?= $row['video'] ?>" type="video/mp4">
                            </video>
                            <button class="play-btn" onclick="handleMedia('<?= $row['id'] ?>', this)">
                                <i class="bi bi-play-fill"></i>
                            </button>
                            <div class="custom-controls">
                                <input type="range" class="progress-bar-custom progress" min="0" max="100" value="0">
                            </div>
                        <?php else: ?>
                            <img src="../admin/uploads/albums/<?= $row['cover'] ?>" alt="Cover">
                        <?php endif; ?>
                    </div>

                    <h5 class="title"><?= htmlspecialchars($row['title']) ?></h5>

                    <div class="meta-info">
                        <span class="badge-item artist-badge"><?= htmlspecialchars($row['artist']) ?></span>
                        <span class="badge-item"><i class="bi bi-calendar3 me-1"></i> <?= htmlspecialchars($row['year']) ?></span>
                        <span class="badge-item"><i class="bi bi-translate me-1"></i> <?= htmlspecialchars($row['language']) ?></span>
                    </div>

                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span style="color: #666; font-size: 0.75rem;">(<?= $row['total_reviews'] ?>)</span>
                    </div>

                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        <i class="bi bi-plus-circle"></i> ADD REVIEW
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-card">
            <h3 id="revTitle" class="mb-1">Album Name</h3>
            <p class="text-dim small">How would you rate this collection?</p>

            <form method="POST">
                <input type="hidden" name="album_id" id="revAlbumId">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-4" rows="4" placeholder="Write your thoughts..." required></textarea>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" onclick="closeReview()">CANCEL</button>
                    <button type="submit" name="submit_review" class="btn btn-primary w-100 rounded-pill" style="background: var(--accent); border:none;">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Album Studio Pro &bull; Optimized Experience</footer>

    <script>
        // Search Filter
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".album-card").forEach(card => {
                card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
            });
        });

        // Play/Pause Control
        function handleMedia(id, btn) {
            const video = document.getElementById('vid-' + id);
            const icon = btn.querySelector('i');

            document.querySelectorAll('video').forEach(v => {
                if (v !== video) {
                    v.pause();
                    const otherBtn = v.closest('.media-wrapper').querySelector('.play-btn i');
                    if (otherBtn) otherBtn.className = 'bi bi-play-fill';
                }
            });

            if (video.paused) {
                video.play();
                icon.className = 'bi bi-pause-fill';
            } else {
                video.pause();
                icon.className = 'bi bi-play-fill';
            }
        }

        // Progress Bar
        document.querySelectorAll('video').forEach(video => {
            const wrapper = video.closest('.media-wrapper');
            const progress = wrapper.querySelector('.progress');
            if(progress) {
                video.addEventListener('timeupdate', () => {
                    progress.value = (video.currentTime / video.duration) * 100;
                });
                progress.addEventListener('input', () => {
                    video.currentTime = (progress.value / 100) * video.duration;
                });
            }
        });

        function openReview(id, title) {
            document.getElementById('revAlbumId').value = id;
            document.getElementById('revTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closeReview() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }
    </script>
</body>
</html>