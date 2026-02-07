<?php
include "../config/db.php";

/* =========================
   HANDLE REVIEW SUBMISSION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {

    $music_id = (int)$_POST['music_id'];
    $rating   = (int)$_POST['rating'];
    $comment  = mysqli_real_escape_string($conn, trim($_POST['comment']));

    if ($music_id > 0 && $rating >= 1 && $rating <= 5 && $comment !== '') {
        mysqli_query(
            $conn,
            "INSERT INTO reviews (music_id, rating, comment) 
             VALUES ($music_id, $rating, '$comment')"
        );
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* =========================
   FETCH MUSIC + RATINGS
========================= */
$query = "
SELECT 
    music.*,
    IFNULL(AVG(reviews.rating), 0) AS avg_rating,
    COUNT(reviews.id) AS total_reviews
FROM music
LEFT JOIN reviews ON reviews.music_id = music.id
GROUP BY music.id
ORDER BY music.id DESC
";

$music = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Music Studio | Reviews & Ratings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* ===== NO STYLE CHANGES ===== */
:root {
    --bg-dark:#080808;
    --card-bg:#111;
    --accent:#ff0055;
    --accent-gradient:linear-gradient(45deg,#ff0055,#ff5e00);
    --text-muted:#777;
}
body{background:var(--bg-dark);color:#fff;font-family:Inter,sans-serif}
.studio-wrapper{width:95%;margin:auto;padding:15px 0}
.header-section{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #1a1a1a;padding-bottom:15px;margin-bottom:20px}
.search-box{background:#151515;border:1px solid #222;color:#fff;border-radius:4px;padding:6px 12px;width:240px;font-size:.8rem}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:15px}
.music-card{background:var(--card-bg);border-radius:8px;padding:15px;text-align:center;border:1px solid #1a1a1a;transition:.3s;position:relative}
.music-card:hover{background:#181818;transform:translateY(-3px);border-color:#333}
.disc-wrapper{width:80px;height:80px;margin:auto;border-radius:50%;background:#000;border:3px solid #1a1a1a;display:flex;align-items:center;justify-content:center;position:relative}
.disc-wrapper i{font-size:2rem;color:var(--accent)}
.playing .disc-wrapper{animation:rotateDisc 3s linear infinite;border-color:var(--accent)}
@keyframes rotateDisc{from{transform:rotate(0)}to{transform:rotate(360deg)}}
.play-trigger{position:absolute;width:30px;height:30px;border-radius:50%;background:var(--accent-gradient);border:none;color:#fff;display:flex;align-items:center;justify-content:center;opacity:0;transition:.3s}
.music-card:hover .play-trigger{opacity:1}
.title{font-size:.85rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.artist{font-size:.75rem;color:var(--text-muted)}
.stars{color:#ffca08;font-size:.7rem}
.review-btn{border:1px solid #333;background:transparent;color:#777;font-size:.65rem;padding:2px 8px;border-radius:4px}
.review-btn:hover{border-color:var(--accent);color:#fff}
audio{width:100%;height:25px;margin-top:10px}
footer{text-align:center;font-size:.65rem;color:#333;padding:30px}
.modal-content{background:#111;border:1px solid #333;color:#fff}
.form-control,.form-select{background:#1a1a1a;border:1px solid #333;color:#fff}
</style>
</head>
<body>

<div class="studio-wrapper">
<div class="header-section">
<h2 class="fw-bold">MUSIC<span style="color:var(--accent)">STUDIO</span></h2>
<div class="d-flex gap-2">
<input type="text" id="search" class="search-box" placeholder="Search track...">
<a href="javascript:history.back()" class="btn btn-dark btn-sm border-secondary">
<i class="bi bi-arrow-left"></i> Back
</a>
</div>
</div>

<div class="grid" id="musicGrid">
<?php if(mysqli_num_rows($music)): while($row=mysqli_fetch_assoc($music)): 
$avg = round($row['avg_rating']); ?>
<div class="music-card" data-title="<?= strtolower($row['title']) ?>" data-artist="<?= strtolower($row['artist']) ?>">
<div class="disc-wrapper">
<i class="bi bi-disc-fill"></i>
<button class="play-trigger" onclick="toggleMusic(this)">
<i class="bi bi-play-fill"></i>
</button>
</div>

<p class="title"><?= htmlspecialchars($row['title']) ?></p>
<p class="artist"><?= htmlspecialchars($row['artist']) ?></p>

<div class="stars">
<?php for($i=1;$i<=5;$i++): ?>
<i class="bi <?= ($i <= $avg)?'bi-star-fill':'bi-star' ?>"></i>
<?php endfor ?>
<span>(<?= $row['total_reviews'] ?>)</span>
</div>

<button class="review-btn" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $row['id'] ?>">
<i class="bi bi-chat-left-text"></i> Review
</button>

<audio onplay="handlePlay(this)" onpause="handlePause(this)">
<source src="../admin/uploads/music/<?= htmlspecialchars($row['file']) ?>" type="audio/mpeg">
</audio>

<!-- MODAL -->
<div class="modal fade" id="reviewModal<?= $row['id'] ?>">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<form method="POST">
<div class="modal-header">
<h6><?= htmlspecialchars($row['title']) ?></h6>
<button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="music_id" value="<?= $row['id'] ?>">

<select name="rating" class="form-select form-select-sm mb-2" required>
<option value="5">5 - Excellent</option>
<option value="4">4 - Very Good</option>
<option value="3">3 - Good</option>
<option value="2">2 - Fair</option>
<option value="1">1 - Poor</option>
</select>

<textarea name="comment" class="form-control form-control-sm" rows="3" required></textarea>
</div>

<div class="modal-footer">
<button name="submit_review" class="btn btn-sm w-100" style="background:var(--accent)">Post Review</button>
</div>
</form>
</div>
</div>
</div>
</div>
<?php endwhile; else: ?>
<p class="text-muted text-center">No tracks found</p>
<?php endif; ?>
</div>
</div>

<footer>SOUND ENTERTAINMENT • 2026</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("search").addEventListener("input",function(){
let v=this.value.toLowerCase();
document.querySelectorAll(".music-card").forEach(c=>{
c.style.display=(c.dataset.title+" "+c.dataset.artist).includes(v)?"block":"none";
});
});

function toggleMusic(btn){
let card=btn.closest('.music-card');
let audio=card.querySelector('audio');
document.querySelectorAll('audio').forEach(a=>{
if(a!==audio){a.pause();a.closest('.music-card').classList.remove('playing');}
});
audio.paused?audio.play():audio.pause();
}

function handlePlay(el){
el.closest('.music-card').classList.add('playing');
el.closest('.music-card').querySelector('.play-trigger i').className='bi bi-pause-fill';
}
function handlePause(el){
el.closest('.music-card').classList.remove('playing');
el.closest('.music-card').querySelector('.play-trigger i').className='bi bi-play-fill';
}
</script>
</body>
</html>
