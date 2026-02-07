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

        /* --- Media Area (Image/Vinyl) --- */
        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            background: linear-gradient(45deg, #111, #222);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Uploaded Image as Background */
        .cover-art {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            opacity: 0.5;
            filter: blur(2px);
        }

        .vinyl-disc {
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: radial-gradient(circle, #333 20%, #111 21%, #111 100%);
            border: 2px solid #222;
            position: relative;
            animation: rotate 5s linear infinite;
            animation-play-state: paused;
            z-index: 2;
            background-size: cover;
            background-position: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        /* Center Circle of Vinyl showing the actual image */
        .vinyl-disc::after {
            content: "";
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 35%; height: 35%;
            border-radius: 50%;
            border: 4px solid #111;
            background-image: inherit; /* Takes image from vinyl-disc */
            background-size: cover;
        }

        .music-card.playing .vinyl-disc {
            animation-play-state: running;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* --- Play Button --- */
        .play-btn {
            position: absolute;
            width: 55px;
            height: 55px;
            background: var(--accent-grad);
            border-radius: 50%;
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            cursor: pointer;
            z-index: 10;
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(255, 51, 102, 0.5);
        }

        /* --- Meta Info --- */
        .meta-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 10px;
        }

        .meta-info span {
            font-size: 0.8rem;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .id-tag { color: var(--text-muted); font-size: 0.7rem !important; }
        .title-tag { color: #fff; font-weight: 700; font-size: 0.95rem !important; }
        .artist-tag { color: var(--accent); font-weight: 600; }
        .album-tag, .year-tag { color: var(--text-muted); }

        .stars-display {
            color: #ffd700;
            font-size: 0.8rem;
            margin-bottom: 12px;
        }

        .rev-btn {
            width: 100%;
            padding: 9px;
            border-radius: 10px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            transition: 0.3s;
            margin-bottom: 8px;
        }

        .rev-btn:hover { background: var(--accent); }

        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 7px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.75rem;
            transition: 0.3s;
        }

        .download-btn:hover { background: #333; color: #fff; }

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
            border-radius: 24px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #333;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .star-rating input { display: none; }
        .star-rating label { font-size: 2.5rem; color: #333; cursor: pointer; transition: 0.2s; }
        .star-rating label:hover, .star-rating label:hover~label, .star-rating input:checked~label { color: #ffd700; }

        footer { text-align: center; padding: 40px; color: #444; font-size: 0.8rem; }
    </style>
</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search tracks or artists...">
                <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)): 
                $avg = round($row['avg_rating'], 1);
                // Check if image exists, else use a placeholder
                $img_path = !empty($row['image']) ? "../admin/uploads/thumbnails/" . $row['image'] : "https://via.placeholder.com/300x300?text=No+Cover";
            ?>
                <div class="music-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                    <div class="media-wrapper">
                        <img src="<?= $img_path ?>" class="cover-art" alt="blur">
                        
                        <div class="vinyl-disc" style="background-image: url('<?= $img_path ?>');"></div>
                        
                        <button class="play-btn" onclick="toggleAudio('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>

                    <div class="meta-info">
                        <span class="id-tag">#<?= htmlspecialchars($row['id']) ?></span>
                        <span class="title-tag"><?= htmlspecialchars($row['title']) ?></span>
                        <span class="artist-tag"><?= htmlspecialchars($row['artist']) ?></span>
                        <span class="album-tag">Album: <?= htmlspecialchars($row['album']) ?></span>
                        <span class="year-tag">Year: <?= htmlspecialchars($row['year']) ?></span>
                    </div>

                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span style="color: #666; font-size: 0.7rem; margin-left: 5px;">(<?= $row['total_reviews'] ?>)</span>
                    </div>

                    <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        <i class="bi bi-chat-dots me-2"></i>LEAVE A REVIEW
                    </button>

                    <?php if (!empty($row['video'])): ?>
                        <a href="../admin/uploads/music/<?= $row['video'] ?>" download class="download-btn">
                            <i class="bi bi-cloud-arrow-down"></i> Get Video Version
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
            <h5 class="text-center mb-1" id="revTitle">Track Name</h5>
            <p class="text-center text-muted small mb-4">How was the sound quality?</p>
            
            <form method="POST">
                <input type="hidden" name="music_id" id="revMusicId">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Share your thoughts..." required></textarea>
                <div class="row g-2">
                    <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeReview()">CLOSE</button></div>
                    <div class="col-6"><button type="submit" name="submit_review" class="btn btn-primary w-100" style="background: var(--accent); border:none;">POST</button></div>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Music Studio Pro &bull; Experience Premium Sound</footer>

    <script>
        function toggleAudio(id, btn) {
            const audio = document.getElementById('audio-' + id);
            const card = btn.closest('.music-card');
            const icon = btn.querySelector('i');

            document.querySelectorAll('audio').forEach(a => {
                if (a !== audio) {
                    a.pause();
                    const otherCard = a.closest('.music-card');
                    otherCard.classList.remove('playing');
                    otherCard.querySelector('.play-btn i').className = 'bi bi-play-fill';
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

        function closeReview() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }

        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".music-card").forEach(card => {
                card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
            });
        });
    </script>
</body>
</html>