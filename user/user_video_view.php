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
    header("Location: ".$_SERVER['PHP_SELF']);
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
<title>Video Studio Pro</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#0b0b0b;
    --card:#151515;
    --accent:#ff3366;
    --grad:linear-gradient(135deg,#ff3366,#ff9933);
    --text:#f5f5f5;
    --muted:#888;
}
body{
    margin:0;
    background:var(--bg);
    color:var(--text);
    font-family:'Inter',sans-serif;
}
.wrapper{
    width:95%;
    margin:auto;
    padding:30px 0;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}
.search{
    background:#1e1e1e;
    border:1px solid #333;
    color:#fff;
    padding:8px 14px;
    border-radius:10px;
    width:260px;
}
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
    gap:25px;
}

/* ===== Card ===== */
.cardx{
    background:var(--card);
    border-radius:20px;
    padding:12px;
    transition:.3s;
}
.cardx:hover{transform:translateY(-6px);}

/* ===== Media (IMAGE STYLE) ===== */
.media{
    position:relative;
    aspect-ratio:1/1;
    border-radius:18px;
    overflow:hidden;
    background:#000;
    margin-bottom:12px;
}
.media img,
.media video{
    width:100%;
    height:100%;
    object-fit:cover;
    position:absolute;
}
.media img{z-index:2;transition:.4s;}
.play{
    position:absolute;
    z-index:5;
    inset:0;
    margin:auto;
    width:65px;
    height:65px;
    border-radius:50%;
    border:none;
    background:var(--grad);
    color:#fff;
    font-size:2rem;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 0 30px rgba(255,51,102,.7);
}

/* ===== Text ===== */
.title{
    font-weight:700;
    margin:0;
}
.meta{
    font-size:.75rem;
    color:var(--muted);
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin:6px 0;
}
.meta span{
    background:#222;
    padding:3px 6px;
    border-radius:6px;
}
.stars{color:#ffd700;font-size:.8rem}

/* ===== Button ===== */
.btn-review{
    width:100%;
    background:#222;
    border:none;
    color:#fff;
    padding:8px;
    border-radius:10px;
    font-size:.8rem;
}
.btn-review:hover{background:var(--accent)}

/* ===== Review Modal ===== */
#overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.9);
    backdrop-filter:blur(8px);
    z-index:999;
    align-items:center;
    justify-content:center;
}
.review-box{
    background:#151515;
    padding:25px;
    border-radius:20px;
    width:90%;
    max-width:400px;
}
.star-rating{
    display:flex;
    flex-direction:row-reverse;
    justify-content:center;
    font-size:2.5rem;
}
.star-rating input{display:none}
.star-rating label{color:#333;cursor:pointer}
.star-rating input:checked~label,
.star-rating label:hover,
.star-rating label:hover~label{color:#ffd700}
</style>
</head>

<body>
<div class="wrapper">
    <div class="header">
        <h4>Video<span style="color:var(--accent)">Studio</span></h4>
        <input id="search" class="search" placeholder="Search...">
    </div>

    <div class="grid" id="grid">
    <?php while($v=mysqli_fetch_assoc($videos)):
        $thumb = $v['thumbnail']
            ? "../admin/uploads/video_thumbnails/".$v['thumbnail']
            : "../assets/img/default.jpg";
        $avg = round($v['avg_rating']);
    ?>
    <div class="cardx" data-search="<?= strtolower($v['title'].' '.$v['artist']) ?>">
        <div class="media">
            <img src="<?= $thumb ?>" id="t<?= $v['id'] ?>">
            <video id="v<?= $v['id'] ?>" loop muted>
                <source src="../admin/uploads/videos/<?= $v['file'] ?>">
            </video>
            <button class="play" onclick="playVid(<?= $v['id'] ?>,this)">
                <i class="bi bi-play-fill"></i>
            </button>
        </div>

        <p class="title"><?= htmlspecialchars($v['title']) ?></p>
        <div class="meta">
            <span><?= $v['artist'] ?></span>
            <span><?= $v['album'] ?></span>
            <span><?= $v['year'] ?></span>
        </div>

        <div class="stars">
            <?php for($i=1;$i<=5;$i++) echo $i<=$avg?'★':'☆'; ?>
            (<?= $v['total_reviews'] ?>)
        </div>

        <button class="btn-review mt-2"
            onclick="openReview(<?= $v['id'] ?>,'<?= addslashes($v['title']) ?>')">
            ADD REVIEW
        </button>
    </div>
    <?php endwhile; ?>
    </div>
</div>

<!-- ===== Review ===== -->
<div id="overlay">
<div class="review-box">
<h5 id="rvTitle" class="text-center"></h5>
<form method="post">
<input type="hidden" name="video_id" id="rvId">
<div class="star-rating my-3">
<input id="s5" type="radio" name="rating" value="5" required><label for="s5">★</label>
<input id="s4" type="radio" name="rating" value="4"><label for="s4">★</label>
<input id="s3" type="radio" name="rating" value="3"><label for="s3">★</label>
<input id="s2" type="radio" name="rating" value="2"><label for="s2">★</label>
<input id="s1" type="radio" name="rating" value="1"><label for="s1">★</label>
</div>
<textarea name="comment" class="form-control bg-dark text-white mb-3" required></textarea>
<div class="d-flex gap-2">
<button type="button" class="btn btn-secondary w-50" onclick="closeReview()">Cancel</button>
<button type="submit" name="submit_review" class="btn w-50" style="background:var(--accent)">Post</button>
</div>
</form>
</div>
</div>

<script>
/* Search */
search.oninput=()=> {
    let v=search.value.toLowerCase();
    document.querySelectorAll('.cardx').forEach(c=>{
        c.style.display=c.dataset.search.includes(v)?'block':'none';
    });
};

/* Play Logic */
function playVid(id,btn){
    let v=document.getElementById('v'+id);
    let t=document.getElementById('t'+id);
    document.querySelectorAll('video').forEach(x=>{
        if(x!==v){
            x.pause();
            x.closest('.media').querySelector('img').style.opacity=1;
            x.closest('.media').querySelector('.play i').className='bi bi-play-fill';
        }
    });
    if(v.paused){
        v.play();
        t.style.opacity=0;
        btn.innerHTML='<i class="bi bi-pause-fill"></i>';
    }else{
        v.pause();
        t.style.opacity=1;
        btn.innerHTML='<i class="bi bi-play-fill"></i>';
    }
}

/* Review */
function openReview(id,title){
    rvId.value=id;
    rvTitle.innerText=title;
    overlay.style.display='flex';
}
function closeReview(){overlay.style.display='none';}
</script>
</body>
</html>
