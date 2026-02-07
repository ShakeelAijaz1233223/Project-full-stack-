<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = $_POST['music_id'];
    $rating = $_POST['rating'];
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
    <title>Music Studio | Pro Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0d0d0d;
            --card: #1b1b1b;
            --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #f5f5f5;
            --text-muted: #999;
            --shadow: rgba(0,0,0,0.8);
        }

        body {
            background: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 25px 0;
        }

        /* --- Header --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
            padding-bottom: 15px;
            margin-bottom: 30px;
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
        }

        .btn-back:hover {
            background: var(--accent);
            color: #fff;
        }

        /* --- Grid & Cards --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .music-card {
            background: var(--card);
            border-radius: 20px;
            padding: 12px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 10px 20px var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .music-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent);
        }

        /* --- Visualizer / Media Wrapper --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Spinning Record Effect on hover or play */
        .record-icon {
            font-size: 4rem;
            color: #222;
            transition: 0.5s;
        }

        .music-card.playing .record-icon {
            color: var(--accent);
            animation: spin 3s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* --- Play Button --- */
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 10;
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.5);
            opacity: 0;
        }

        .music-card:hover .play-btn {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
        }

        /* --- Controls --- */
        .custom-controls {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.9));
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 11;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .progress-range {
            flex: 1;
            height: 5px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        /* --- Text Styling --- */
        .title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-info {
            display: flex;
            gap: 8px;
            margin: 5px 0 10px;
            font-size: 0.75rem;
            flex-wrap: wrap;
        }

        .meta-info span {
            background: rgba(255,255,255,0.08);
            padding: 2px 8px;
            border-radius: 5px;
            color: var(--text-muted);
        }

        .artist-tag {
            color: var(--accent) !important;
            font-weight: 600;
            background: rgba(255, 51, 102, 0.1) !important;
        }

        .stars-display {
            color: #ffd700;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .rev-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 8px;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        .dl-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 6px;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            font-size: 0.75rem;
            text-decoration: none;
            transition: 0.3s;
        }

        .dl-btn:hover {
            background: #333;
            color: #fff;
        }

        /* --- Review Modal --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-box {
            background: #151515;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #333;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 5px;
            margin-bottom: 20px;
        }

        .star-rating label { font-size: 2.5rem; color: #333; cursor: pointer; }
        .star-rating input { display: none; }
        .star-rating label:hover, .star-rating label:hover~label, .star-rating input:checked~label { color: #ffd700; }

        footer { text-align: center; padding: 40px; color: #444; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">Music<span style="color: var(--accent);">Studio</span></h4>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search title or artist...">
            <a href="index.php" class="btn-back"><i class="bi bi-house"></i> Home</a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php while ($row = mysqli_fetch_assoc($music)): 
            $avg = round($row['avg_rating'], 1);
        ?>
            <div class="music-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                <div class="media-wrapper">
                    <i class="bi bi-disc record-icon"></i>
                    
                    <button class="play-btn" onclick="handleAudio('<?= $row['id'] ?>', this)">
                        <i class="bi bi-play-fill"></i>
                    </button>

                    <div class="custom-controls">
                        <input type="range" class="progress-range" min="0" max="100" value="0">
                        <button onclick="toggleMute('<?= $row['id'] ?>', this)"><i class="bi bi-volume-up"></i></button>
                    </div>
                </div>

                <p class="title"><?= htmlspecialchars($row['title']) ?></p>
                
                <div class="meta-info">
                    <span class="artist-tag"><?= htmlspecialchars($row['artist']) ?></span>
                    <span><?= htmlspecialchars($row['album']) ?></span>
                    <span><?= htmlspecialchars($row['year']) ?></span>
                </div>

                <div class="stars-display">
                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span style="color: #666; font-size: 0.7rem;">(<?= $row['total_reviews'] ?>)</span>
                </div>

                <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                    <i class="bi bi-chat-square-text me-2"></i>REVIEW
                </button>

                <?php if (!empty($row['video'])): ?>
                    <a href="../admin/uploads/music/<?= $row['video']; ?>" download class="dl-btn">
                        <i class="bi bi-cloud-arrow-down"></i> Download Video
                    </a>
                <?php endif; ?>

                <audio id="aud-<?= $row['id'] ?>">
                    <source src="../admin/uploads/music/<?= $row['file'] ?>" type="audio/mpeg">
                </audio>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-box">
        <h5 class="text-center mb-1" id="revTitle">Music Name</h5>
        <p class="text-center text-muted small mb-4">Rate your experience</p>
        
        <form method="POST">
            <input type="hidden" name="music_id" id="revMusicId">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write feedback..." required></textarea>
            <div class="row g-2">
                <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeReview()">CANCEL</button></div>
                <div class="col-6"><button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color:#fff; border:none;">SUBMIT</button></div>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 Music Studio Pro &bull; Optimized Experience</footer>

<script>
    // Search Filter
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".music-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });

    // Play/Pause Logic
    function handleAudio(id, btn) {
        const audio = document.getElementById('aud-' + id);
        const card = btn.closest('.music-card');
        const icon = btn.querySelector('i');

        // Stop other audios
        document.querySelectorAll('audio').forEach(a => {
            if (a !== audio) {
                a.pause();
                a.closest('.music-card').classList.remove('playing');
                a.closest('.music-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
            }
        });

        if (audio.paused) {
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
        const wrapper = audio.closest('.music-card');
        const progress = wrapper.querySelector('.progress-range');
        
        audio.addEventListener('timeupdate', () => {
            progress.value = (audio.currentTime / audio.duration) * 100 || 0;
        });

        progress.addEventListener('input', () => {
            audio.currentTime = (progress.value / 100) * audio.duration;
        });
    });

    function toggleMute(id, btn) {
        const audio = document.getElementById('aud-' + id);
        audio.muted = !audio.muted;
        btn.innerHTML = audio.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
    }

    // Modal
    function openReview(id, title) {
        document.getElementById('revMusicId').value = id;
        document.getElementById('revTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }

    function closeReview() {
        document.getElementById('reviewOverlay').style.display = 'none';
    }
</script>

</body>
</html>