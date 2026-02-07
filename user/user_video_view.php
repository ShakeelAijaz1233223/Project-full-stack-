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
<title>Video Studio | Pro Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --bg-dark: #080808;
    --card-bg: #121212;
    --accent: #ff0055;
    --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
    --text-muted: #888888;
}
body {
    background-color: var(--bg-dark);
    color: #fff;
    font-family: 'Inter', sans-serif;
    margin: 0;
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
    border-radius: 4px;
    padding: 6px 15px;
    width: 250px;
    font-size: 0.85rem;
}
.btn-back, .btn-delete, .btn-edit {
    background: #1a1a1a;
    border: 1px solid #222;
    color: #fff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: 0.3s ease;
}
.btn-back:hover, .btn-delete:hover, .btn-edit:hover {
    background: #222;
    border-color: var(--accent);
    color: #fff;
}
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}
.card {
    background: var(--card-bg);
    border: 1px solid transparent;
    border-radius: 10px;
    overflow: hidden;
    transition: 0.3s ease;
    text-align: center;
    padding: 10px;
    position: relative;
}
.card:hover {
    transform: translateY(-5px);
    background: #1a1a1a;
    border-color: #333;
}
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 1/1;
    background: #000;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.play-overlay {
    position: absolute;
    width: 35px;
    height: 35px;
    background: var(--accent-gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: 0.3s;
    z-index: 10;
    border: none;
    color: white;
}
.card:hover .play-overlay { opacity: 1; }
.title { font-weight: 600; font-size: 0.85rem; margin-bottom: 1px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stars-row { color: #ffca08; font-size: 0.7rem; margin: 4px 0; }
.btn-rev-pop { background: #000; color: #ccc; border: 1px solid #222; font-size: 0.65rem; width: 100%; padding: 4px; border-radius: 4px; margin-top: 5px; font-weight: 600; }
.btn-rev-pop:hover { background: var(--accent); color: white; }
.card-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 6px; z-index: 20; }
#reviewOverlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.9); z-index:10000; align-items:center; justify-content:center; }
.review-modal { background:#111; width:90%; max-width:380px; padding:30px; border-radius:20px; border:1px solid #222; }
.star-input { display:flex; flex-direction:row-reverse; justify-content:center; gap:10px; }
.star-input input { display:none; }
.star-input label { font-size:2.5rem; color:#222; cursor:pointer; }
.star-input label:hover, .star-input label:hover~label, .star-input input:checked~label { color:#ffca08; }
footer { padding:40px; text-align:center; font-size:0.7rem; color:#444; }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">VIDEO<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search videos...">
            <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success py-2" style="font-size:0.8rem;"><?= $msg ?></div>
    <?php endif; ?>

    <div class="grid" id="videoGrid">
        <?php while($row = mysqli_fetch_assoc($videos)):
            $avg = round($row['avg_rating'],1);
        ?>
            <div class="card album-card" data-title="<?= strtolower($row['title']); ?>">
                <div class="card-actions">
                    <a href="?delete=<?= $row['id'];?>" class="btn-delete" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                    <a href="edit_video.php?id=<?= $row['id'];?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
                </div>

                <div class="media-wrapper">
                    <video id="vid-<?= $row['id'];?>" preload="metadata" playsinline muted loop>
                        <source src="../admin/uploads/videos/<?= $row['file'];?>" type="video/mp4">
                    </video>
                    <button class="play-overlay" onclick="togglePlay('<?= $row['id'];?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>

                <div class="card-body">
                    <div class="title"><?= htmlspecialchars($row['title']);?></div>
                    <div class="stars-row">
                        <?php for($i=1;$i<=5;$i++) echo ($i<=$avg)?'★':'☆';?>
                        <span class="ms-1 text-muted">(<?= $row['total_reviews'];?>)</span>
                    </div>
                    <button class="btn-rev-pop" onclick="popReview('<?= $row['id'];?>','<?= addslashes($row['title']);?>')">REVIEW</button>
                </div>
            </div>
        <?php endwhile;?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-1" id="popTitle">Video Name</h5>
        <p class="text-center text-muted small mb-4">Leave a rating and comment</p>
        <form method="POST">
            <input type="hidden" name="video_id" id="popId">
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

<footer>&copy; 2026 VIDEO STUDIO &bull; SYSTEM PRO</footer>

<script>
// Search
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".album-card").forEach(card=>{
        card.style.display = card.dataset.title.includes(val)?"block":"none";
    });
});

// Play Video
function togglePlay(id, btn){
    const video = document.getElementById('vid-'+id);
    const icon = btn.querySelector('i');
    document.querySelectorAll('video').forEach(v=>{if(v!==video) v.pause();});
    if(video.paused){ video.play(); icon.className='bi bi-pause-fill'; }
    else { video.pause(); icon.className='bi bi-play-fill'; }
}

// Review Modal
function popReview(id,title){
    document.getElementById('popId').value = id;
    document.getElementById('popTitle').innerText = title;
    document.getElementById('reviewOverlay').style.display = 'flex';
}
function closePop(){ document.getElementById('reviewOverlay').style.display='none'; }

    // Video Player logic
    document.querySelectorAll('.media-wrapper').forEach(wrapper => {
        const video = wrapper.querySelector('video');
        if (!video) return;

        const playBtn = wrapper.querySelector('.play-btn');
        const progress = wrapper.querySelector('.progress');
        const muteBtn = wrapper.querySelector('.mute-btn');
        const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');

        playBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (video.paused) {
                document.querySelectorAll('video').forEach(v => v.pause());
                video.play();
                playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
            } else {
                video.pause();
                playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            }
        });

        video.addEventListener('timeupdate', () => {
            progress.value = (video.currentTime / video.duration) * 100;
        });

        progress.addEventListener('input', () => {
            video.currentTime = (progress.value / 100) * video.duration;
        });

        muteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            video.muted = !video.muted;
            muteBtn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
        });

        fullscreenBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (video.requestFullscreen) video.requestFullscreen();
        });
    });
</script>

</body>
</html>
