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
    <title>Music Studio | Pro Dashboard</title>
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

        /* --- Header Section --- */
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
        }

        /* --- Grid & Music Cards --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        .music-card {
            background: var(--card);
            border-radius: 20px;
            padding: 12px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 10px 20px var(--shadow);
            transition: all 0.3s ease;
        }

        .music-card:hover {
            transform: translateY(-8px);
            border-color: var(--accent);
        }

        /* --- Media Wrapper Fix --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cover-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.6;
            transition: 0.5s;
        }

        .music-card.playing .cover-img {
            opacity: 0.3;
            filter: blur(3px);
        }

        .vinyl-disc {
            width: 70%;
            height: 70%;
            border-radius: 50%;
            background: radial-gradient(circle, transparent 20%, #000 21%, #111 100%);
            border: 2px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 2;
            animation: rotate 5s linear infinite;
            animation-play-state: paused;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .music-card.playing .vinyl-disc {
            animation-play-state: running;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .play-btn {
            position: absolute;
            width: 50px;
            height: 50px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.5);
        }

        /* --- Meta Info --- */
        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }

        .meta-tag {
            background: rgba(255, 255, 255, 0.05);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        .track-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            display: block;
            margin-bottom: 2px;
        }

        .artist-name {
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        .stars-display { color: #ffd700; font-size: 0.8rem; margin-bottom: 10px; }

        .rev-btn {
            width: 100%;
            padding: 8px;
            border-radius: 10px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .rev-btn:hover { background: var(--accent); }

        /* --- Review Overlay --- */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center; justify-content: center;
        }

        .review-box {
            background: #151515;
            padding: 30px;
            border-radius: 20px;
            width: 90%; max-width: 400px;
            border: 1px solid #333;
        }

        .star-rating {
            display: flex; flex-direction: row-reverse;
            justify-content: center; gap: 5px; margin-bottom: 15px;
        }
        .star-rating input { display: none; }
        .star-rating label { font-size: 2rem; color: #333; cursor: pointer; }
        .star-rating label:hover, .star-rating label:hover~label, .star-rating input:checked~label { color: #ffd700; }

        footer { text-align: center; padding: 40px; color: #444; font-size: 0.8rem; }
    </style>
</head>
<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search...">
                <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)): 
                $avg = round((float)$row['avg_rating'], 1);
                $coverPath = "../admin/uploads/music_covers/" . ($row['cover'] ? $row['cover'] : 'default.jpg');
            ?>
                <div class="music-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                    <div class="media-wrapper">
                        <img src="<?= $coverPath ?>" class="cover-img" alt="Cover">
                        
                        <div class="vinyl-disc"></div>
                        
                        <button class="play-btn" onclick="toggleAudio('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>

                    <span class="track-title"><?= htmlspecialchars($row['title']) ?></span>
                    <span class="artist-name"><?= htmlspecialchars($row['artist']) ?></span>

                    <div class="meta-info">
                        <span class="meta-tag">Album: <?= htmlspecialchars($row['album']) ?></span>
                        <span class="meta-tag">Year: <?= htmlspecialchars($row['year']) ?></span>
                    </div>

                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <small style="color: #555">(<?= $row['total_reviews'] ?>)</small>
                    </div>

                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        LEAVE A REVIEW
                    </button>

                    <?php if (!empty($row['video'])): ?>
                        <a href="../admin/uploads/music/<?= $row['video'] ?>" download class="btn btn-sm btn-outline-secondary w-100" style="font-size: 0.7rem; border-radius: 10px;">
                            <i class="bi bi-camera-video me-1"></i> Video Version
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
        <div class="review-box">
            <h5 class="text-white text-center mb-1" id="revTitle">Track Name</h5>
            <p class="text-muted text-center small mb-4">Rate this track</p>
            <form method="POST">
                <input type="hidden" name="music_id" id="revMusicId">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write review..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="closeReview()">Cancel</button>
                    <button type="submit" name="submit_review" class="btn btn-primary w-100" style="background: var(--accent); border:none;">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Music Studio Pro</footer>

    <script>
        function toggleAudio(id, btn) {
            const audio = document.getElementById('audio-' + id);
            const card = btn.closest('.music-card');
            const icon = btn.querySelector('i');

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

        function openReview(id, title) {
            document.getElementById('revMusicId').value = id;
            document.getElementById('revTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closeReview() { document.getElementById('reviewOverlay').style.display = 'none'; }

        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".music-card").forEach(card => {
                card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
            });
        });
    </script>
</body>
</html>