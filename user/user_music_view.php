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
// Note: Ensure your table name is 'music' or 'music_studio' as per your DB
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
        }

        body {
            background: var(--bg);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .studio-wrapper { width: 95%; margin: 0 auto; padding: 25px 0; }

        /* Header */
        .header-section {
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 30px;
        }

        .search-box {
            background: #1f1f1f; border: 1px solid #333; color: white;
            border-radius: 10px; padding: 8px 16px; width: 280px;
        }

        /* Music Card Design */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .music-card {
            background: var(--card); border-radius: 20px; padding: 15px;
            border: 1px solid #2a2a2a; transition: 0.3s;
        }

        .music-card:hover { transform: translateY(-5px); border-color: var(--accent); }

        /* Vinyl Area */
        .media-wrapper {
            position: relative; width: 100%; aspect-ratio: 16/9; /* Video friendly aspect */
            background: linear-gradient(45deg, #111, #222);
            border-radius: 12px; overflow: hidden; margin-bottom: 15px;
            display: flex; align-items: center; justify-content: center;
        }

        .vinyl-disc {
            width: 100px; height: 100px; border-radius: 50%;
            background: radial-gradient(circle, #333 20%, #000 21%, #111 100%);
            border: 5px solid #222; animation: rotate 5s linear infinite;
            animation-play-state: paused;
        }

        .music-card.playing .vinyl-disc { animation-play-state: running; }

        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .play-btn {
            position: absolute; width: 50px; height: 50px;
            background: var(--accent-grad); border-radius: 50%;
            border: none; color: white; font-size: 1.5rem; display: flex;
            align-items: center; justify-content: center; cursor: pointer;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.4);
        }

        /* Meta Info Grid Styling */
        .meta-info {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
            margin-bottom: 15px; font-size: 0.8rem;
        }

        .meta-info span {
            background: rgba(255,255,255,0.05); padding: 5px 10px;
            border-radius: 6px; border: 1px solid #222;
            color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .meta-info .title-tag { grid-column: span 2; color: white; font-weight: bold; font-size: 0.9rem; border-color: #333; }
        .meta-info .artist-tag { color: var(--accent); font-weight: 600; }

        .stars-display { color: #ffd700; font-size: 0.85rem; margin-bottom: 10px; }

        /* Buttons */
        .rev-btn {
            width: 100%; padding: 10px; border-radius: 10px; border: none;
            background: #333; color: white; font-weight: 600; font-size: 0.8rem;
            transition: 0.3s; margin-bottom: 8px;
        }
        .rev-btn:hover { background: var(--accent); }

        .download-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 8px; border-radius: 10px;
            background: rgba(255,255,255,0.05); color: var(--text-muted);
            text-decoration: none; font-size: 0.75rem; border: 1px dashed #444;
        }

        /* Review Overlay */
        #reviewOverlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); backdrop-filter: blur(10px);
            z-index: 9999; align-items: center; justify-content: center;
        }
        .review-box {
            background: #111; padding: 30px; border-radius: 20px;
            width: 90%; max-width: 400px; border: 1px solid var(--accent);
        }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search...">
                <a href="javascript:history.back()" class="btn btn-outline-light btn-sm rounded-pill px-3">Back</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)): 
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="music-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                    <div class="media-wrapper">
                        <div class="vinyl-disc"></div>
                        <button class="play-btn" onclick="toggleAudio('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>

                    <div class="meta-info">
                        <span class="title-tag"><i class="bi bi-music-note-beamed me-2"></i><?= htmlspecialchars($row['title']) ?></span>
                        <span class="artist-tag"><i class="bi bi-person me-1"></i><?= htmlspecialchars($row['artist']) ?></span>
                        <span class="album-tag"><i class="bi bi-disc me-1"></i><?= htmlspecialchars($row['album']) ?></span>
                        <span class="year-tag"><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($row['year']) ?></span>
                        
                    </div>

                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <small class="ms-2 text-muted">(<?= $row['total_reviews'] ?> reviews)</small>
                    </div>

                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        ADD REVIEW
                    </button>

                    <?php if (!empty($row['video'])): ?>
                        <a href="../admin/uploads/music/<?= $row['video'] ?>" download class="download-btn">
                            <i class="bi bi-camera-video"></i> Download Video
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
            <h5 class="text-center text-white mb-4" id="revTitle">Track Name</h5>
            <form method="POST">
                <input type="hidden" name="music_id" id="revMusicId">
                <div class="star-rating mb-4">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write something..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark w-100" onclick="closeReview()">CANCEL</button>
                    <button type="submit" name="submit_review" class="btn btn-primary w-100" style="background: var(--accent); border:none;">SUBMIT</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Audio Logic
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

        // Search
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".music-card").forEach(card => {
                card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
            });
        });
    </script>
</body>
</html>