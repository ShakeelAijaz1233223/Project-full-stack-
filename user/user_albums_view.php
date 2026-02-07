<?php
session_start();
include "../config/db.php";

// 1. Handle Review Submission (Targeting video_reviews table for albums)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $video_id = $_POST['album_id']; // Using album_id in video_id column
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    mysqli_query($conn, "INSERT INTO video_reviews (video_id, rating, comment) VALUES ('$video_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// 2. Fetch Albums with Ratings
$query = "SELECT albums.*, 
          (SELECT AVG(rating) FROM video_reviews WHERE video_reviews.video_id = albums.id) as avg_rating,
          (SELECT COUNT(*) FROM video_reviews WHERE video_reviews.video_id = albums.id) as total_reviews
          FROM albums ORDER BY created_at DESC";
$albums = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* [Your existing CSS] */
        :root { --bg-dark: #080808; --card-bg: #121212; --accent: #ff0055; --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00); }
        body { background-color: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 20px 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
        .card { background: var(--card-bg); border-radius: 10px; padding: 10px; position: relative; border: 1px solid #1a1a1a; transition: 0.3s; }
        .media-wrapper { position: relative; width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 8px; overflow: hidden; }
        video { width: 100%; height: 100%; object-fit: cover; }
        .play-overlay { position: absolute; width: 35px; height: 35px; background: var(--accent-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; z-index: 10; border: none; color: white; }
        .card:hover .play-overlay { opacity: 1; }
        
        /* New Review Styles */
        .stars-row { color: #ffca08; font-size: 0.7rem; margin: 4px 0; }
        .btn-rev-pop { background: #1a1a1a; color: #fff; border: 1px solid #333; font-size: 0.65rem; width: 100%; padding: 4px; border-radius: 4px; margin-top: 5px; }
        .btn-rev-pop:hover { background: var(--accent); border-color: var(--accent); }

        #reviewOverlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); z-index: 10000; align-items: center; justify-content: center;
        }
        .review-modal { background: #111; width: 90%; max-width: 380px; padding: 30px; border-radius: 20px; border: 1px solid #222; }
        .star-input { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; }
        .star-input input { display: none; }
        .star-input label { font-size: 2.5rem; color: #222; cursor: pointer; }
        .star-input label:hover, .star-input label:hover ~ label, .star-input input:checked ~ label { color: #ffca08; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section d-flex justify-content-between">
        <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search albums...">
            <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        <?php while ($row = mysqli_fetch_assoc($albums)): 
            $avg = round($row['avg_rating'], 1);
        ?>
        <div class="card album-card" data-title="<?= strtolower($row['title']); ?>">
            <div class="media-wrapper">
                <?php if (!empty($row['video'])): ?>
                    <video id="vid-<?= $row['id']; ?>" loop muted playsinline poster="../admin/uploads/albums/<?= $row['cover']; ?>">
                        <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                    </video>
                    <button class="play-overlay" onclick="togglePlay('<?= $row['id']; ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                <?php else: ?>
                    <img src="../admin/uploads/albums/<?= $row['cover']; ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="title"><?= $row['title'] ?></div>
                <div class="stars-row">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span class="ms-1 text-muted">(<?= $row['total_reviews'] ?>)</span>
                </div>
                <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">RATE ALBUM</button>
                <?php if (!empty($row['audio'])): ?>
                    <audio id="aud-<?= $row['id']; ?>"><source src="../admin/uploads/albums/<?= $row['audio']; ?>"></audio>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-1" id="popTitle">Album Name</h5>
        <p class="text-center text-muted small mb-4">How is the sound & visual?</p>
        <form method="POST">
            <input type="hidden" name="album_id" id="popId">
            <div class="star-input mb-4">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write feedback..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                <button type="submit" name="submit_review" class="btn btn-danger w-100">Post</button>
            </div>
        </form>
    </div>
</div>

<script>
    function popReview(id, title) {
        document.getElementById('popId').value = id;
        document.getElementById('popTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }
    function closePop() { document.getElementById('reviewOverlay').style.display = 'none'; }
    
    // Toggle Play Fix
    function togglePlay(id, btn) {
        const audio = document.getElementById('aud-' + id);
        const video = document.getElementById('vid-' + id);
        const icon = btn.querySelector('i');
        document.querySelectorAll('audio, video').forEach(m => { if(m !== audio && m !== video) m.pause(); });
        if(audio.paused) { 
            audio.play(); if(video) { video.muted=true; video.play(); } 
            icon.className = 'bi bi-pause-fill';
        } else { 
            audio.pause(); if(video) video.pause(); 
            icon.className = 'bi bi-play-fill';
        }
    }
</script>
</body>
</html>