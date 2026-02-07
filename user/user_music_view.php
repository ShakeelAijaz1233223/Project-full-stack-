<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = mysqli_real_escape_string($conn, $_POST['music_id']);
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    mysqli_query($conn, "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// Fetch Music with Average Ratings
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
    <title>Music Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --bg: #080808;
            --card: #141414;
            --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #ffffff;
            --text-muted: #888;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.01em;
        }

        .studio-wrapper {
            max-width: 1300px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* --- Header Section --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #222;
        }

        .search-box {
            background: #1a1a1a;
            border: 1px solid #333;
            color: white;
            border-radius: 50px;
            padding: 10px 25px;
            width: 320px;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(255, 51, 102, 0.2);
        }

        /* --- Grid Layout --- */
        .music-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        /* --- Music Card --- */
        .music-card {
            background: var(--card);
            border-radius: 24px;
            padding: 20px;
            border: 1px solid #222;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .music-card:hover {
            transform: translateY(-12px);
            border-color: var(--accent);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }

        /* --- Vinyl Animation --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: inset 0 0 50px rgba(255,255,255,0.05);
        }

        .vinyl-disc {
            width: 85%;
            height: 85%;
            border-radius: 50%;
            background: radial-gradient(circle, #222 15%, #000 16%, #111 25%, #050505 100%);
            border: 4px solid #1a1a1a;
            position: relative;
            animation: rotate 6s linear infinite;
            animation-play-state: paused;
        }

        /* Grooves on vinyl */
        .vinyl-disc::after {
            content: "";
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 30%; height: 30%;
            border-radius: 50%;
            background: var(--accent-grad);
            opacity: 0.8;
        }

        .music-card.playing .vinyl-disc { animation-play-state: running; }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .play-btn {
            position: absolute;
            width: 65px; height: 65px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(255, 51, 102, 0.3);
            transition: 0.3s;
        }

        .play-btn:hover { transform: scale(1.1); }

        /* --- Meta Info --- */
        .track-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 5px; }
        .meta-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 15px; }
        .tag { background: var(--glass); font-size: 0.7rem; padding: 4px 10px; border-radius: 6px; color: var(--text-muted); }

        /* --- Rating & Buttons --- */
        .stars-display { color: #ffd700; margin-bottom: 15px; font-size: 0.9rem; }
        .btn-action {
            width: 100%; border-radius: 12px; padding: 10px;
            font-weight: 600; font-size: 0.85rem; transition: 0.3s;
            margin-bottom: 10px; border: none;
        }
        .btn-review { background: #fff; color: #000; }
        .btn-review:hover { background: var(--accent); color: #fff; }

        /* --- Progress Bar --- */
        .controls-overlay {
            position: absolute; bottom: 0; width: 100%;
            padding: 15px; background: linear-gradient(transparent, rgba(0,0,0,0.8));
            opacity: 0; transition: 0.3s;
        }
        .media-wrapper:hover .controls-overlay { opacity: 1; }
        .progress-bar-custom { width: 100%; height: 4px; accent-color: var(--accent); cursor: pointer; }

        /* --- Review Modal --- */
        #reviewOverlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.9); backdrop-filter: blur(15px);
            z-index: 9999; align-items: center; justify-content: center;
        }
        .review-card {
            background: #111; padding: 40px; border-radius: 30px; width: 90%; max-width: 450px;
            border: 1px solid #222; text-align: center;
        }

        footer { text-align: center; padding: 60px 0; color: #444; border-top: 1px solid #111; margin-top: 50px; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <header class="header-section">
        <div>
            <h2 class="m-0 fw-bold">MUSIC<span style="color: var(--accent)">STUDIO</span></h2>
            <p class="text-muted small m-0">Hi-Res Audio Dashboard</p>
        </div>
        <div class="d-flex gap-3">
            <input type="text" id="search" class="search-box" placeholder="Search tracks or artists...">
            <a href="javascript:history.back()" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </header>

    <div class="music-grid" id="musicGrid">
        <?php while ($row = mysqli_fetch_assoc($music)): 
            $avg = round($row['avg_rating'], 1); 
        ?>
        <div class="music-card" data-search="<?= strtolower($row['title'].' '.$row['artist']); ?>">
            <div class="media-wrapper">
                <div class="vinyl-disc"></div>
                <button class="play-btn" onclick="toggleAudio('<?= $row['id'] ?>', this)">
                    <i class="bi bi-play-fill"></i>
                </button>
                <div class="controls-overlay">
                    <input type="range" class="progress-bar-custom progress" min="0" max="100" value="0">
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted"><i class="bi bi-volume-up"></i></small>
                        <button class="bg-transparent border-0 text-white" onclick="muteAudio('<?= $row['id'] ?>', this)">
                            <i class="bi bi-speaker"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="track-title"><?= htmlspecialchars($row['title']) ?></div>
            <div class="meta-tags">
                <span class="tag" style="color:var(--accent)">@<?= htmlspecialchars($row['artist']) ?></span>
                <span class="tag"><?= htmlspecialchars($row['album']) ?></span>
                <span class="tag"><?= htmlspecialchars($row['year']) ?></span>
            </div>

            <div class="stars-display">
                <?php for($i=1;$i<=5;$i++) echo ($i<=$avg)?'★':'☆'; ?>
                <span style="color:#444; font-size:0.75rem; margin-left:5px;">(<?= $row['total_reviews'] ?>)</span>
            </div>

            <button class="btn-action btn-review" onclick="openReview('<?= $row['id'] ?>','<?= addslashes($row['title']) ?>')">
                <i class="bi bi-chat-right-text me-2"></i>LEAVE REVIEW
            </button>

            <?php if(!empty($row['video'])): ?>
                <a href="../admin/uploads/music/<?= $row['video'] ?>" download class="btn btn-dark w-100 rounded-3 small py-2" style="font-size:0.7rem; border:1px solid #222">
                    <i class="bi bi-camera-video me-2"></i>DOWNLOAD VIDEO VERSION
                </a>
            <?php endif; ?>

            <audio id="audio-<?= $row['id'] ?>" preload="none">
                <source src="../admin/uploads/music/<?= $row['file'] ?>" type="audio/mpeg">
            </audio>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-card">
        <h3 id="revTitle">Track Name</h3>
        <p class="text-muted mb-4">How was the experience?</p>
        <form method="POST">
            <input type="hidden" name="music_id" id="revMusicId">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-0 p-3 mb-4" rows="4" placeholder="Share your feedback..." required style="border-radius:15px;"></textarea>
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill" onclick="closeReview()">CANCEL</button>
                <button type="submit" name="submit_review" class="btn btn-primary w-100 rounded-pill" style="background: var(--accent); border:none;">POST</button>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 Music Studio Pro &bull; Experience Premium Sound</footer>

<script>
// Search
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase();
    document.querySelectorAll(".music-card").forEach(card => {
        card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
    });
});

// Audio Logic
function toggleAudio(id, btn){
    const audio = document.getElementById('audio-'+id);
    const card = btn.closest('.music-card');
    const icon = btn.querySelector('i');
    
    // Stop others
    document.querySelectorAll('audio').forEach(a => {
        if(a !== audio){
            a.pause();
            a.closest('.music-card').classList.remove('playing');
            a.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
        }
    });

    if(audio.paused){
        audio.play();
        card.classList.add('playing');
        icon.className = 'bi bi-pause-fill';
    } else {
        audio.pause();
        card.classList.remove('playing');
        icon.className = 'bi bi-play-fill';
    }
}

// Progress Bar
document.querySelectorAll('audio').forEach(audio => {
    const card = audio.closest('.music-card');
    const progress = card.querySelector('.progress');
    audio.addEventListener('timeupdate', () => {
        if(audio.duration) progress.value = (audio.currentTime / audio.duration) * 100;
    });
    progress.addEventListener('input', () => {
        audio.currentTime = (progress.value / 100) * audio.duration;
    });
});

function muteAudio(id, btn){
    const audio = document.getElementById('audio-'+id);
    audio.muted = !audio.muted;
    btn.innerHTML = audio.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-speaker"></i>';
}

function openReview(id, title){
    document.getElementById('revMusicId').value = id;
    document.getElementById('revTitle').innerText = title;
    document.getElementById('reviewOverlay').style.display = 'flex';
}
function closeReview(){ document.getElementById('reviewOverlay').style.display = 'none'; }
</script>
</body>
</html>