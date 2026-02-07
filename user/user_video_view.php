<?php
session_start();
include "../config/db.php";

// 1. Handle Delete Video
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $video = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM videos WHERE id=$delete_id"));
    if ($video) {
        @unlink("../admin/uploads/videos/" . $video['file']);
        mysqli_query($conn, "DELETE FROM videos WHERE id=$delete_id");
        $msg = "Video deleted successfully!";
    }
}

// 2. Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $video_id = $_POST['video_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO video_reviews (video_id, rating, comment) VALUES ('$video_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// 3. Fetch videos with average rating
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
<title>Video Studio | Live Motion</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #ff0055, #7000ff);
        --bg-dark: #080808;
        --card-bg: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent: #ff0055;
    }

    /* LIVE MOVING BACKGROUND */
    body {
        margin: 0;
        color: #fff;
        font-family: 'Inter', sans-serif;
        background: linear-gradient(-45deg, #080808, #1a000a, #000a1a, #080808);
        background-size: 400% 400%;
        animation: gradientMove 15s ease infinite;
        min-height: 100vh;
    }

    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .studio-wrapper { width: 95%; margin: 0 auto; padding: 20px 0; }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-bottom: 15px;
    }

    .search-box {
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--glass-border);
        color: white;
        border-radius: 12px;
        padding: 10px 15px;
        width: 250px;
        backdrop-filter: blur(10px);
    }

    /* Cards - Same as Albums */
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }

    .album-card {
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 15px;
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }

    .album-card:hover {
        transform: translateY(-10px);
        border-color: var(--accent);
        box-shadow: 0 15px 30px rgba(255, 0, 85, 0.2);
    }

    /* Media Wrapper */
    .media-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 16/9;
        background: #000;
        border-radius: 18px;
        margin-bottom: 15px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
    }

    video { width: 100%; height: 100%; object-fit: cover; }

    .play-overlay {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 50px; height: 50px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center; justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: 0.3s;
        z-index: 10;
        border: none;
        color: white;
        font-size: 1.5rem;
    }
    .album-card:hover .play-overlay { opacity: 1; }

    /* Moving Visualizer Overlay (Live Indicator) */
    .visualizer {
        position: absolute;
        top: 10px; right: 10px;
        display: none;
        gap: 3px;
        height: 15px;
        align-items: flex-end;
        background: rgba(0,0,0,0.6);
        padding: 5px 8px;
        border-radius: 6px;
        z-index: 11;
    }
    .playing .visualizer { display: flex; }
    .playing .media-wrapper { border-color: var(--accent); box-shadow: 0 0 15px rgba(255,0,85,0.3); }

    .bar {
        width: 3px;
        background: var(--accent);
        border-radius: 2px;
        animation: bounce 0.5s ease-in-out infinite alternate;
    }
    .bar:nth-child(2) { animation-delay: 0.2s; height: 100%; }
    .bar:nth-child(3) { animation-delay: 0.4s; height: 60%; }
    .bar:nth-child(1) { height: 80%; }

    @keyframes bounce { from { height: 4px; } to { height: 15px; } }

    /* Actions */
    .card-actions {
        position: absolute;
        top: 10px;
        left: 10px;
        display: flex;
        gap: 5px;
        z-index: 15;
    }
    .btn-action {
        background: rgba(0,0,0,0.5);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-action:hover { background: var(--accent); border-color: var(--accent); }

    /* Text elements */
    .title { font-weight: 800; font-size: 1rem; margin-bottom: 2px; color: #fff; }
    .stars-row { color: #ffca08; font-size: 0.75rem; margin-bottom: 12px; }

    .btn-rev-pop {
        background: var(--primary-gradient);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 10px;
        width: 100%;
        text-transform: uppercase;
        transition: 0.3s;
    }
    .btn-rev-pop:hover { filter: brightness(1.2); transform: scale(1.02); }

    /* Modal Styling */
    #reviewOverlay {
        display: none; position: fixed; top:0; left:0; width:100%; height:100%;
        background: rgba(0,0,0,0.9); z-index:10000; align-items:center; justify-content:center;
    }
    .review-modal { background:#111; width:90%; max-width:380px; padding:30px; border-radius:24px; border:1px solid var(--glass-border); }
    .star-input { display:flex; flex-direction:row-reverse; justify-content:center; gap:10px; margin-bottom: 20px; }
    .star-input input { display:none; }
    .star-input label { font-size:2.5rem; color:#222; cursor:pointer; }
    .star-input label:hover, .star-input label:hover~label, .star-input input:checked~label { color:#ffca08; }

    footer { padding:60px; text-align:center; font-size:0.7rem; color:rgba(255,255,255,0.2); }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search videos...">
            <a href="index.php" class="btn btn-dark btn-sm rounded-pill px-3">Back</a>
        </div>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success bg-dark text-white border-success mb-4" style="font-size:0.85rem;"><?= $msg ?></div>
    <?php endif; ?>

    <div class="grid" id="videoGrid">
        <?php while($row = mysqli_fetch_assoc($videos)):
            $avg = round($row['avg_rating'],1);
        ?>
            <div class="album-card" data-title="<?= strtolower($row['title']); ?>">
                <div class="card-actions">
                    <a href="?delete=<?= $row['id'];?>" class="btn-action" onclick="return confirm('Delete video?');"><i class="bi bi-trash"></i></a>
                    <a href="edit_video.php?id=<?= $row['id'];?>" class="btn-action"><i class="bi bi-pencil"></i></a>
                </div>

                <div class="media-wrapper">
                    <div class="visualizer">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    
                    <video id="vid-<?= $row['id'];?>" preload="metadata" playsinline muted loop>
                        <source src="../admin/uploads/videos/<?= $row['file'];?>" type="video/mp4">
                    </video>
                    
                    <button class="play-overlay" onclick="togglePlay('<?= $row['id'];?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="title"><?= htmlspecialchars($row['title']);?></div>
                    <div class="stars-row">
                        <?php for($i=1;$i<=5;$i++) echo ($i<=$avg)?'★':'☆';?>
                        <span class="ms-1 text-muted">(<?= $row['total_reviews'];?>)</span>
                    </div>
                    <button class="btn-rev-pop" onclick="popReview('<?= $row['id'];?>','<?= addslashes($row['title']);?>')">Rate Video</button>
                </div>
            </div>
        <?php endwhile;?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-1" id="popTitle">Video Name</h5>
        <p class="text-center text-muted small mb-4">Leave your rating</p>
        <form method="POST">
            <input type="hidden" name="video_id" id="popId">
            <div class="star-input">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mt-4 mb-3" placeholder="Write feedback..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                <button type="submit" name="submit_review" class="btn btn-danger w-100" style="background: var(--primary-gradient); border:none;">Post</button>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 VIDEO STUDIO &bull; MOTION SYSTEM</footer>

<script>
// Search
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".album-card").forEach(card=>{
        card.style.display = card.dataset.title.includes(val)?"block":"none";
    });
});

// Play Logic
function togglePlay(id, btn){
    const card = btn.closest('.album-card');
    const video = document.getElementById('vid-'+id);
    const icon = btn.querySelector('i');
    
    // Stop others
    document.querySelectorAll('video').forEach(v=>{
        if(v!==video) {
            v.pause();
            v.closest('.album-card').classList.remove('playing');
            v.closest('.album-card').querySelector('.play-overlay i').className='bi bi-play-fill';
        }
    });

    if(video.paused){
        video.play();
        card.classList.add('playing');
        icon.className='bi bi-pause-fill';
    } else {
        video.pause();
        card.classList.remove('playing');
        icon.className='bi bi-play-fill';
    }
}

// Modal
function popReview(id,title){
    document.getElementById('popId').value = id;
    document.getElementById('popTitle').innerText = title;
    document.getElementById('reviewOverlay').style.display = 'flex';
}
function closePop(){ document.getElementById('reviewOverlay').style.display='none'; }
</script>

</body>
</html>