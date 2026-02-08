<?php
include "../config/db.php";

/* ===== Handle Review ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $video_id = (int)$_POST['video_id'];
    $rating   = (int)$_POST['rating'];
    $comment  = mysqli_real_escape_string($conn, $_POST['comment']);

    mysqli_query($conn, "
        INSERT INTO video_reviews (video_id, rating, comment)
        VALUES ($video_id, $rating, '$comment')
    ");
    header("Location: ".$_SERVER['PHP_SELF']."?status=success");
    exit;
}

/* ===== Fetch Videos ===== */
 $videos = mysqli_query($conn, "
    SELECT v.*,
    (SELECT AVG(rating) FROM video_reviews WHERE video_id=v.id) avg_rating,
    (SELECT COUNT(*) FROM video_reviews WHERE video_id=v.id) total_reviews
    FROM videos v
    ORDER BY v.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Video Studio | Pro Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --bg: #0d0d0d;
    --card: #1b1b1b;
    --accent: #ff3366;
    --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
    --text-main: #f5f5f5;
    --text-muted: #999;
    --shadow: rgba(0, 0, 0, 0.8);
}

body{
    margin: 0;
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
    overflow-x: hidden;
}

.studio-wrapper {
    width: 95%;
    margin: 0 auto;
    padding: 25px 0;
}

/* --- Header Section (Same as Album/Music) --- */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #222;
    padding-bottom: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
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
    white-space: nowrap;
}

.btn-back:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Grid & Card Design --- */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.card-video {
    background: var(--card);
    border-radius: 20px;
    padding: 12px;
    border: 1px solid #2a2a2a;
    transition: all 0.3s ease;
    position: relative;
}

.card-video:hover {
    transform: translateY(-8px);
    border-color: var(--accent);
    box-shadow: 0 10px 20px var(--shadow);
}

/* --- Media Wrapper (1:1 Cinematic Style) --- */
.media-container {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 15px;
    overflow: hidden;
    background: #000;
    margin-bottom: 15px;
}

.media-container img,
.media-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0; left: 0;
}

.media-container img {
    z-index: 2;
    transition: opacity 0.5s ease;
}

.play-trigger {
    position: absolute;
    z-index: 5;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    background: var(--accent-grad);
    color: #fff;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px rgba(255, 51, 102, 0.5);
    transition: 0.3s;
}

.play-trigger:hover { transform: translate(-50%, -50%) scale(1.1); }

/* --- Content Styling --- */
.v-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.v-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
}

.v-meta span {
    background: rgba(255, 255, 255, 0.05);
    padding: 3px 8px;
    border-radius: 6px;
}

.artist-tag {
    color: var(--accent) !important;
    font-weight: 600;
    background: rgba(255, 51, 102, 0.1) !important;
}

.stars { color: #ffd700; font-size: 0.8rem; margin-bottom: 12px; }

.rev-btn {
    width: 100%;
    background: #222;
    border: none;
    color: #fff;
    padding: 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: 0.3s;
}

.rev-btn:hover { background: var(--accent); }

/* --- Modal Styling --- */
#overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(10px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.review-box {
    background: #151515;
    padding: 30px;
    border-radius: 25px;
    width: 90%;
    max-width: 400px;
    border: 1px solid #333;
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 8px;
}

.star-rating input { display: none; }
.star-rating label { font-size: 2.5rem; color: #333; cursor: pointer; transition: 0.2s; }
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label { color: #ffd700; }

footer { text-align: center; padding: 50px; color: #444; font-size: 0.8rem; }

/* --- RESPONSIVE ADJUSTMENTS --- */
@media (max-width: 1200px) {
    .studio-wrapper {
        width: 96%;
    }
}

@media (max-width: 992px) {
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .header-section {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-box {
        width: 100%;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 10px !important;
    }
}

@media (max-width: 768px) {
    .studio-wrapper {
        width: 98%;
        padding: 20px 0;
    }
    
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .card-video {
        padding: 10px;
    }
    
    .play-trigger {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .v-title {
        font-size: 0.9rem;
    }
    
    .v-meta {
        font-size: 0.7rem;
        gap: 5px;
    }
    
    .stars {
        font-size: 0.75rem;
    }
    
    .rev-btn {
        padding: 12px;
        font-size: 0.85rem;
    }
}

@media (max-width: 576px) {
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }
    
    .card-video {
        padding: 8px;
    }
    
    .play-trigger {
        width: 45px;
        height: 45px;
        font-size: 1.3rem;
    }
    
    .v-title {
        font-size: 0.85rem;
    }
    
    .v-meta {
        font-size: 0.65rem;
    }
    
    .stars {
        font-size: 0.7rem;
    }
    
    .review-box {
        padding: 20px;
        width: 95%;
    }
    
    .star-rating label {
        font-size: 2rem;
    }
}

@media (max-width: 480px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .studio-wrapper {
        padding: 15px 0;
    }
    
    .header-section {
        margin-bottom: 20px;
        padding-bottom: 10px;
    }
    
    h4 {
        font-size: 1.2rem;
    }
    
    .btn-back {
        padding: 8px 15px;
        font-size: 0.85rem;
    }
    
    .media-container {
        aspect-ratio: 16 / 9;
    }
    
    .review-box {
        padding: 15px;
    }
    
    .star-rating label {
        font-size: 1.8rem;
    }
    
    footer {
        padding: 30px 15px;
        font-size: 0.75rem;
    }
}

@media (max-width: 360px) {
    .grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .review-box {
        padding: 15px;
    }
    
    .star-rating label {
        font-size: 1.6rem;
    }
}
</style>
</head>

<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">Video<span style="color:var(--accent)">Studio</span></h4>
        <div class="d-flex gap-2">
            <input id="search" class="search-box" placeholder="Search videos, artists...">
            <a href="index.php" class="btn-back"><i class="bi bi-house"></i> Home</a>
        </div>
    </div>

    <div class="grid" id="grid">
    <?php while($v=mysqli_fetch_assoc($videos)):
        $thumb = $v['thumbnail']
            ? "../admin/uploads/video_thumbnails/".$v['thumbnail']
            : "../assets/img/default.jpg";
        $avg = round($v['avg_rating']);
    ?>
    <div class="card-video" data-search="<?= strtolower($v['title'].' '.$v['artist']) ?>">
        <div class="media-container">
            <img src="<?= $thumb ?>" id="t<?= $v['id'] ?>">
            <video id="v<?= $v['id'] ?>" loop playsinline>
                <source src="../admin/uploads/videos/<?= $v['file'] ?>" type="video/mp4">
            </video>
            <button class="play-trigger" onclick="playVid(<?= $v['id'] ?>, this)">
                <i class="bi bi-play-fill"></i>
            </button>
        </div>

        <p class="v-title"><?= htmlspecialchars($v['title']) ?></p>
        
        <div class="v-meta">
            <span class="artist-tag"><?= htmlspecialchars($v['artist']) ?></span>
            <span><?= htmlspecialchars($v['album']) ?></span>
            <span><i class="bi bi-calendar3 me-1"></i> <?= $v['year'] ?></span>
        </div>

        <div class="stars">
            <?php for($i=1;$i<=5;$i++) echo $i<=$avg?'★':'☆'; ?>
            <span style="color: #555; font-size: 0.7rem;">(<?= $v['total_reviews'] ?>)</span>
        </div>

        <button class="rev-btn" 
                onclick="openReview(<?= $v['id'] ?>,'<?= addslashes($v['title']) ?>')">
            <i class="bi bi-plus-circle me-2"></i>ADD REVIEW
        </button>
    </div>
    <?php endwhile; ?>
    </div>
</div>

<div id="overlay">
    <div class="review-box">
        <h5 id="rvTitle" class="text-center mb-1"></h5>
        <p class="text-center text-muted small mb-4">Rate your cinematic experience</p>
        
        <form method="post">
            <input type="hidden" name="video_id" id="rvId">
            <div class="star-rating mb-4">
                <input id="s5" type="radio" name="rating" value="5" required><label for="s5">★</label>
                <input id="s4" type="radio" name="rating" value="4"><label for="s4">★</label>
                <input id="s3" type="radio" name="rating" value="3"><label for="s3">★</label>
                <input id="s2" type="radio" name="rating" value="2"><label for="s2">★</label>
                <input id="s1" type="radio" name="rating" value="1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write your review..." required></textarea>
            <div class="row g-2">
                <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeReview()">Cancel</button></div>
                <div class="col-6"><button type="submit" name="submit_review" class="btn btn-primary w-100" style="background:var(--accent); border:none;">Post</button></div>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 Video Studio Pro &bull; Next-Gen Entertainment</footer>

<script>
/* Real-time Search */
document.getElementById('search').oninput = function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll('.card-video').forEach(card => {
        card.style.display = card.dataset.search.includes(val) ? 'block' : 'none';
    });
};

/* Video Control Logic */
function playVid(id, btn) {
    let video = document.getElementById('v' + id);
    let thumb = document.getElementById('t' + id);
    let icon = btn.querySelector('i');

    // Pause all other videos first
    document.querySelectorAll('video').forEach(v => {
        if (v !== video) {
            v.pause();
            let otherThumb = v.closest('.media-container').querySelector('img');
            let otherBtnIcon = v.closest('.media-container').querySelector('.play-trigger i');
            otherThumb.style.opacity = 1;
            otherBtnIcon.className = 'bi bi-play-fill';
        }
    });

    // Toggle Current Video
    if (video.paused) {
        video.play();
        thumb.style.opacity = 0;
        icon.className = 'bi bi-pause-fill';
    } else {
        video.pause();
        thumb.style.opacity = 1;
        icon.className = 'bi bi-play-fill';
    }
}

/* Review Functions */
function openReview(id, title) {
    document.getElementById('rvId').value = id;
    document.getElementById('rvTitle').innerText = title;
    document.getElementById('overlay').style.display = 'flex';
}
function closeReview() { document.getElementById('overlay').style.display = 'none'; }
</script>

</body>
</html>