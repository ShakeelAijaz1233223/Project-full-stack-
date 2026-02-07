<?php
session_start();
include "../config/db.php";

// 1. Handle Review Submission (Targeting NEW album_reviews table)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $album_id = $_POST['album_id']; 
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Updated table name and column name
    $sql = "INSERT INTO album_reviews (album_id, rating, comment) VALUES ('$album_id', '$rating', '$comment')";
    
    if(mysqli_query($conn, $sql)){
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit();
}

// 2. Fetch Albums with NEW table ratings
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
    <title>Albums Studio | Fixed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --bg-dark: #080808; --card-bg: #121212; --accent: #ff0055; --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00); }
        body { background-color: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 20px 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
        .card { background: var(--card-bg); border-radius: 12px; padding: 12px; border: 1px solid #1a1a1a; transition: 0.3s; position: relative; }
        .card:hover { border-color: var(--accent); }
        .media-wrapper { position: relative; width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 8px; overflow: hidden; }
        video { width: 100%; height: 100%; object-fit: cover; }
        .play-overlay { position: absolute; top:50%; left:50%; transform:translate(-50%, -50%); width: 40px; height: 40px; background: var(--accent-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; z-index: 10; border: none; color: white; }
        .card:hover .play-overlay { opacity: 1; }
        .stars-row { color: #ffca08; font-size: 0.75rem; margin: 8px 0 4px; }
        .btn-rev-pop { background: #1a1a1a; color: #fff; border: 1px solid #333; font-size: 0.7rem; width: 100%; padding: 6px; border-radius: 6px; font-weight: bold; }
        .btn-rev-pop:hover { background: var(--accent); border-color: var(--accent); }
        #reviewOverlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .review-modal { background: #111; width: 90%; max-width: 400px; padding: 30px; border-radius: 20px; border: 1px solid #222; }
        .star-input { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .star-input input { display: none; }
        .star-input label { font-size: 2.5rem; color: #222; cursor: pointer; transition: 0.2s; }
        .star-input label:hover, .star-input label:hover ~ label, .star-input input:checked ~ label { color: #ffca08; }
        .title { font-weight: 600; font-size: 0.9rem; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
        <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm px-3"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid">
        <?php while ($row = mysqli_fetch_assoc($albums)): 
            $avg = round($row['avg_rating'], 1);
        ?>
        <div class="card">
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

            <div class="card-body p-0">
                <div class="title text-white"><?= htmlspecialchars($row['title']) ?></div>
                <div class="stars-row">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span class="ms-1 text-muted">(<?= $row['total_reviews'] ?>)</span>
                </div>
                <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                   <i class="bi bi-star-half me-1"></i> RATE & REVIEW
                </button>
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
        <div class="text-center mb-4">
            <h5 class="fw-bold m-0" id="popTitle">Album Name</h5>
            <small class="text-muted">Rate your experience</small>
        </div>
        <form method="POST">
            <input type="hidden" name="album_id" id="popId">
            <div class="star-input">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write feedback..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-dark w-100" onclick="closePop()">CANCEL</button>
                <button type="submit" name="submit_review" class="btn btn-danger w-100 fw-bold">POST REVIEW</button>
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
    
    function togglePlay(id, btn) {
        const audio = document.getElementById('aud-' + id);
        const video = document.getElementById('vid-' + id);
        const icon = btn.querySelector('i');
        
        document.querySelectorAll('audio, video').forEach(m => { 
            if(m !== audio && m !== video) { m.pause(); } 
        });

        if(audio && audio.paused) { 
            audio.play(); 
            if(video) { video.muted=true; video.play(); } 
            icon.className = 'bi bi-pause-fill';
        } else if(audio) { 
            audio.pause(); 
            if(video) video.pause(); 
            icon.className = 'bi bi-play-fill';
        } else if(video) {
            if(video.paused) { video.muted=false; video.play(); icon.className = 'bi bi-pause-fill'; }
            else { video.pause(); icon.className = 'bi bi-play-fill'; }
        }
    }
</script>
</body>
</html>