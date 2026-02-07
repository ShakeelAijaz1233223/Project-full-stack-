<?php
session_start();
include "../config/db.php";

// 1. Handle Review Submission (Secure way)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $album_id = mysqli_real_escape_string($conn, $_POST['album_id']);
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    $review_query = "INSERT INTO album_reviews (album_id, rating, comment) VALUES ('$album_id', '$rating', '$comment')";
    
    if(mysqli_query($conn, $review_query)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
        exit();
    }
}

// 2. Fetch albums with Average Ratings & Join data
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
    <title>Albums Studio | Sound System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg: #0d0d0d;
            --card: #1b1b1b;
            --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #f5f5f5;
            --text-muted: #999;
            --shadow: rgba(0,0,0,0.6);
        }

        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 25px 0; }

        /* Header */
        .header-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 30px; }
        .search-box { background: #1f1f1f; border: 1px solid #333; color: #fff; border-radius: 10px; padding: 8px 16px; width: 280px; transition: 0.3s; }
        .search-box:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 8px var(--accent); }
        .btn-back { background: #222; color: #fff; padding: 8px 18px; border-radius: 12px; text-decoration: none; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .btn-back:hover { background: var(--accent); }

        /* Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; }
        .album-card { background: var(--card); border-radius: 20px; padding: 12px; border: 1px solid #2a2a2a; transition: 0.3s; }
        .album-card:hover { transform: translateY(-6px); border-color: var(--accent); }

        /* Media */
        .media-wrapper { position: relative; width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 15px; overflow: hidden; margin-bottom: 12px; }
        .media-wrapper img, .media-wrapper video { width: 100%; height: 100%; object-fit: cover; }
        
        .play-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50px; height: 50px; background: var(--accent-grad); border-radius: 50%; border: none; color: #fff; display: flex; align-items: center; justify-content: center; opacity: 0; cursor: pointer; z-index: 5; transition: 0.3s; }
        .album-card:hover .play-btn { opacity: 1; }

        .custom-controls { position: absolute; bottom: 0; left: 0; right: 0; display: flex; align-items: center; padding: 10px; background: rgba(0,0,0,0.8); opacity: 0; transition: 0.3s; }
        .media-wrapper:hover .custom-controls { opacity: 1; }

        .title { font-size: 1rem; font-weight: 700; margin-bottom: 2px; }
        .artist { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 10px; }
        .stars-row { color: #ffd700; font-size: 0.8rem; margin-bottom: 12px; }

        .btn-rev-pop { background: var(--accent); border: none; color: #fff; width: 100%; padding: 8px; border-radius: 10px; font-weight: 600; }

        /* Review Modal */
        #reviewOverlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; }
        .review-modal { background: var(--card); width: 90%; max-width: 400px; padding: 30px; border-radius: 22px; border: 1px solid #333; }
        .star-input { display: flex; flex-direction: row-reverse; justify-content: center; gap: 8px; }
        .star-input input { display: none; }
        .star-input label { font-size: 2.5rem; color: #333; cursor: pointer; }
        .star-input label:hover, .star-input label:hover~label, .star-input input:checked~label { color: #ffd700; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search by album or artist...">
            <a href="index.php" class="btn-back"><i class="bi bi-house"></i> Home</a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        <?php while ($row = mysqli_fetch_assoc($albums)): 
            $avg = round($row['avg_rating'], 1);
        ?>
            <div class="album-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                <div class="media-wrapper">
                    <?php if (!empty($row['video'])): ?>
                        <video id="vid-<?= $row['id']; ?>" preload="metadata" poster="../admin/uploads/albums/<?= $row['cover']; ?>">
                            <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                        </video>
                        <button class="play-btn" onclick="togglePlay('<?= $row['id']; ?>', this)"><i class="bi bi-play-fill"></i></button>
                        <div class="custom-controls">
                            <input type="range" class="progress w-100" min="0" max="100" value="0" step="0.1">
                        </div>
                    <?php else: ?>
                        <img src="../admin/uploads/albums/<?= $row['cover']; ?>" alt="Cover">
                    <?php endif; ?>
                </div>

                <div class="card-body p-1">
                    <div class="title text-truncate"><?= htmlspecialchars($row['title']); ?></div>
                    <div class="artist text-truncate"><?= htmlspecialchars($row['artist']); ?></div>
                    
                    <div class="stars-row">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="ms-1 text-muted small">(<?= $row['total_reviews'] ?>)</span>
                    </div>

                    <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        <i class="bi bi-chat-dots me-2"></i>Rate Album
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-1" id="popTitle">Album Name</h5>
        <p class="text-center text-muted small mb-4">Rate your listening experience</p>
        <form method="POST">
            <input type="hidden" name="album_id" id="popId">
            <div class="star-input mb-4">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="What do you think about this album?" required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                <button type="submit" name="submit_review" class="btn btn-danger w-100" style="background: var(--accent);">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. Live Search
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase().trim();
        document.querySelectorAll(".album-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });

    // 2. Video Player Controller
    function togglePlay(id, btn) {
        const video = document.getElementById('vid-' + id);
        const icon = btn.querySelector('i');

        // Pause all other videos
        document.querySelectorAll('video').forEach(v => {
            if (v !== video) {
                v.pause();
                const otherBtn = v.closest('.media-wrapper').querySelector('.play-btn i');
                if(otherBtn) otherBtn.className = 'bi bi-play-fill';
            }
        });

        if (video.paused) {
            video.play();
            icon.className = 'bi bi-pause-fill';
        } else {
            video.pause();
            icon.className = 'bi bi-play-fill';
        }

        // Progress update
        const progress = video.closest('.media-wrapper').querySelector('.progress');
        video.ontimeupdate = () => {
            progress.value = (video.currentTime / video.duration) * 100;
        };
        progress.oninput = () => {
            video.currentTime = (progress.value / 100) * video.duration;
        };
    }

    // 3. Modal Controls
    function popReview(id, title) {
        document.getElementById('popId').value = id;
        document.getElementById('popTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }

    function closePop() {
        document.getElementById('reviewOverlay').style.display = 'none';
    }
</script>

</body>
</html>