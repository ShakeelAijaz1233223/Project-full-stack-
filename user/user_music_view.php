<?php
include "../config/db.php";

// 1. Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = $_POST['music_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// 2. Fetch Music with Average Ratings
$query = "SELECT music.*, 
          (SELECT AVG(rating) FROM reviews WHERE reviews.music_id = music.id) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE reviews.music_id = music.id) as total_reviews
          FROM music ORDER BY id DESC";
$music = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Music Studio | Home</title>
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
    background: var(--bg-dark);
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

/* Grid & Cards */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}

.card {
    background: var(--card-bg);
    border: 1px solid transparent;
    border-radius: 12px;
    padding: 12px;
    position: relative;
    text-align: center;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
}

.disc-wrapper {
    position: relative;
    width: 70px;
    height: 70px;
    margin: 0 auto 10px;
    border-radius: 50%;
    background: #000;
    border: 3px solid #222;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.3s;
}

.disc-wrapper i { font-size: 1.8rem; color: var(--accent); }

.play-trigger {
    position: absolute;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: none;
    background: var(--accent-gradient);
    color: #fff;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s;
}

.card:hover .play-trigger { opacity: 1; }

.playing .disc-wrapper { animation: rotate 3s linear infinite; border-color: var(--accent); }
@keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.title { font-weight: 600; font-size: 0.85rem; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.artist { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 5px; }

.stars-row {
    color: #ffca08;
    font-size: 0.7rem;
    margin: 4px 0;
}

.btn-rev-pop {
    background: #000;
    color: #ccc;
    border: 1px solid #222;
    font-size: 0.65rem;
    width: 100%;
    padding: 5px;
    border-radius: 5px;
    margin-top: 5px;
    font-weight: 600;
    transition: 0.3s;
}

.btn-rev-pop:hover { background: var(--accent); color: white; }

/* Review Modal */
#reviewOverlay {
    display: none;
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.9);
    z-index:10000;
    align-items:center; justify-content:center;
}

.review-modal {
    background:#111;
    width:90%; max-width:380px;
    padding:30px;
    border-radius:20px;
    border:1px solid #222;
}

.star-input { display:flex; flex-direction:row-reverse; justify-content:center; gap:10px; margin-bottom: 15px; }
.star-input input { display:none; }
.star-input label { font-size:2.5rem; color:#222; cursor:pointer; transition:0.2s; }
.star-input label:hover, .star-input label:hover~label, .star-input input:checked~label { color:#ffca08; }

footer { padding:40px; text-align:center; font-size:0.7rem; color:#444; }
audio { width:100%; margin-top:10px; border-radius:5px; }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search music...">
            <a href="javascript:history.back()" class="btn btn-dark btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php while ($row = mysqli_fetch_assoc($music)):
            $avg = round($row['avg_rating'],1);
        ?>
        <div class="card album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
            <div class="disc-wrapper">
                <i class="bi bi-disc-fill"></i>
                <button class="play-trigger" onclick="togglePlay(this)"><i class="bi bi-play-fill"></i></button>
            </div>
            <div class="title"><?= htmlspecialchars($row['title']); ?></div>
            <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
            <div class="stars-row">
                <?php for($i=1;$i<=5;$i++) echo ($i<=$avg)?'★':'☆'; ?>
                <span class="text-muted ms-1">(<?= $row['total_reviews']; ?>)</span>
            </div>
            <button class="btn-rev-pop" onclick="openReview('<?= $row['id'];?>','<?= addslashes($row['title']);?>')">REVIEW</button>
            <audio>
                <source src="../admin/uploads/music/<?= $row['file'];?>" type="audio/mpeg">
            </audio>
        </div>
        <?php endwhile;?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-2" id="popTitle">Rate Music</h5>
        <form method="POST">
            <input type="hidden" name="music_id" id="popId">
            <div class="star-input">
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

<footer>&copy; 2026 MUSIC STUDIO &bull; SOUND SYSTEM</footer>

<script>
function openReview(id,title){
    document.getElementById('popId').value=id;
    document.getElementById('popTitle').innerText=title;
    document.getElementById('reviewOverlay').style.display='flex';
}
function closePop(){ document.getElementById('reviewOverlay').style.display='none'; }

// Play Music
function togglePlay(btn){
    const card = btn.closest('.album-card');
    const audio = card.querySelector('audio');
    document.querySelectorAll('audio').forEach(a=>{if(a!==audio) {a.pause(); a.closest('.album-card').classList.remove('playing');}});
    if(audio.paused){ audio.play(); card.classList.add('playing'); btn.querySelector('i').className='bi bi-pause-fill'; }
    else{ audio.pause(); card.classList.remove('playing'); btn.querySelector('i').className='bi bi-play-fill'; }
}

// Search
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase();
    document.querySelectorAll(".album-card").forEach(card=>{
        let txt = card.dataset.title + " " + card.dataset.artist;
        card.style.display = txt.includes(val) ? "block" : "none";
    });
});
</script>
</body>
</html>
