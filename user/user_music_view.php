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

// 2. Fetch Music
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
<title>Music Studio | Live Motion</title>
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

    /* 1. LIVE MOVING BACKGROUND */
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

    /* Header */
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

    /* Cards */
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }

    .album-card {
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 20px;
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }

    .album-card:hover {
        transform: translateY(-10px);
        border-color: var(--accent);
        box-shadow: 0 15px 30px rgba(255, 0, 85, 0.2);
    }

    /* Live Visualizer on Disc */
    .disc-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 15px;
        border-radius: 50%;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #1a1a1a;
        box-shadow: 0 8px 16px rgba(0,0,0,0.5);
    }

    .disc-wrapper i { font-size: 2.5rem; color: var(--accent); }

    .play-trigger {
        position: absolute;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        border: none;
        background: var(--primary-gradient);
        color: #fff;
        opacity: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
        z-index: 10;
    }
    .album-card:hover .play-trigger { opacity: 1; }

    /* 2. LIVE MOVING VISUALIZER BARS */
    .visualizer {
        position: absolute;
        bottom: 15px;
        display: none;
        gap: 3px;
        height: 20px;
        align-items: flex-end;
    }
    .playing .visualizer { display: flex; }
    .playing .disc-wrapper { animation: rotate 4s linear infinite; border-color: var(--accent); }
    .playing .disc-wrapper i { display: none; } /* Hide icon when bars are moving */

    .bar {
        width: 4px;
        background: var(--accent);
        border-radius: 2px;
        animation: bounce 0.5s ease-in-out infinite alternate;
    }
    .bar:nth-child(2) { animation-delay: 0.2s; height: 100%; }
    .bar:nth-child(3) { animation-delay: 0.4s; height: 60%; }
    .bar:nth-child(1) { height: 80%; }

    @keyframes bounce {
        from { height: 5px; }
        to { height: 25px; }
    }
    @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

    /* UI elements */
    .title { font-weight: 800; font-size: 1.1rem; margin-bottom: 2px; color: #fff; }
    .artist { font-size: 0.85rem; color: #aaa; margin-bottom: 12px; }
    .stars-row { color: #ffca08; font-size: 0.8rem; margin-bottom: 15px; }

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

    /* Modal */
    #reviewOverlay {
        display: none;
        position: fixed;
        top:0; left:0; width:100%; height:100%;
        background: rgba(0,0,0,0.9);
        z-index:10000;
        align-items:center; justify-content:center;
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
        <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search tracks...">
            <a href="javascript:history.back()" class="btn btn-dark btn-sm rounded-pill px-3">Back</a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php while ($row = mysqli_fetch_assoc($music)):
            $avg = round($row['avg_rating'], 1);
        ?>
        <div class="album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
            <div class="disc-wrapper">
                <i class="bi bi-vinyl-fill"></i>
                <div class="visualizer">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <button class="play-trigger" onclick="togglePlay(this)"><i class="bi bi-play-fill"></i></button>
            </div>
            
            <div class="card-body p-0 text-start">
                <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
                <div class="stars-row">
                    <?php for($i=1;$i<=5;$i++) echo ($i<=$avg)?'★':'☆'; ?>
                    <small class="text-muted ms-2">(<?= $row['total_reviews']; ?>)</small>
                </div>
                <button class="btn-rev-pop" onclick="openReview('<?= $row['id'];?>','<?= addslashes($row['title']);?>')">Rate Track</button>
            </div>

            <audio>
                <source src="../admin/uploads/music/<?= $row['file'];?>" type="audio/mpeg">
            </audio>
        </div>
        <?php endwhile;?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-1" id="popTitle">Rate Music</h5>
        <form method="POST">
            <input type="hidden" name="music_id" id="popId">
            <div class="star-input">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Feedback..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                <button type="submit" name="submit_review" class="btn btn-danger w-100" style="background: var(--primary-gradient); border:none;">Post</button>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 MUSIC STUDIO &bull; SYSTEM ACTIVE</footer>

<script>
function openReview(id,title){
    document.getElementById('popId').value=id;
    document.getElementById('popTitle').innerText=title;
    document.getElementById('reviewOverlay').style.display='flex';
}
function closePop(){ document.getElementById('reviewOverlay').style.display='none'; }

function togglePlay(btn){
    const card = btn.closest('.album-card');
    const audio = card.querySelector('audio');
    
    document.querySelectorAll('audio').forEach(a => {
        if(a !== audio) {
            a.pause(); 
            a.closest('.album-card').classList.remove('playing');
            a.closest('.album-card').querySelector('.play-trigger i').className = 'bi bi-play-fill';
        }
    });

    if(audio.paused){
        audio.play();
        card.classList.add('playing');
        btn.querySelector('i').className='bi bi-pause-fill';
    } else {
        audio.pause();
        card.classList.remove('playing');
        btn.querySelector('i').className='bi bi-play-fill';
    }
}

document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".album-card").forEach(card=>{
        let txt = card.dataset.title + " " + card.dataset.artist;
        card.style.display = txt.includes(val) ? "block" : "none";
    });
});
</script>
</body>
</html>