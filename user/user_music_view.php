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
    <title>Music Studio | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bg: #080808;
            --card: #111;
            --accent: #ff0055;
            --accent-grad: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #888;
        }

        body {
            background: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #222;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .search-box {
            background: #151515;
            border: 1px solid #333;
            color: #fff;
            border-radius: 6px;
            padding: 6px 15px;
            width: 250px;
        }

        .btn-back {
            background: #1a1a1a;
            border: 1px solid #222;
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        /* Grid & Cards */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .album-card {
            background: var(--card);
            border-radius: 15px;
            padding: 12px;
            border: 1px solid #1a1a1a;
            transition: 0.3s;
            position: relative;
        }

        .album-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }

        /* Media Wrapper */
        .disc-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        audio {
            width: 100%;
            display: none;
        }

        /* Play Button */
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: var(--accent-grad);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            cursor: pointer;
            transition: 0.3s;
        }

        .album-card:hover .play-btn {
            opacity: 1;
        }

        /* Playing Animation */
        .playing .disc-wrapper {
            animation: rotate 3s linear infinite;
            border: 3px solid var(--accent);
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Title & Stars */
        .title {
            font-weight: 600;
            font-size: 0.85rem;
            margin: 5px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .stars-display {
            color: #ffca08;
            font-size: 0.75rem;
            margin-bottom: 8px;
        }

        /* Review Button */
        .rev-btn {
            background: #222;
            color: #fff;
            border: none;
            font-size: 0.7rem;
            width: 100%;
            padding: 6px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .rev-btn:hover {
            background: var(--accent);
        }

        /* Review Overlay */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-box {
            background: #151515;
            width: 90%;
            max-width: 400px;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #333;
        }

        /* Star Rating */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2.5rem;
            color: #222;
            cursor: pointer;
            transition: 0.2s;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #ffca08;
        }

        footer {
            text-align: center;
            padding: 40px;
            font-size: 0.7rem;
            color: #444;
        }
    </style>

</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search music...">
                <a href="javascript:history.back()" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    <div class="disc-wrapper">
                        <i class="bi bi-disc-fill"></i>
                        <button class="play-btn" onclick="togglePlay(this)"><i class="bi bi-play-fill"></i></button>
                    </div>
                    <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                    <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="text-white opacity-50 ms-1">(<?= $row['total_reviews']; ?>)</span>
                    </div>
                    <button class="rev-btn" onclick="openReview('<?= $row['id']; ?>','<?= addslashes($row['title']); ?>')">REVIEW</button>
                    <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                            <div class="custom-controls">
                                <input type="range" class="progress" min="0" max="100" value="0">
                                <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                                <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                    <audio id="audio-<?= $row['id']; ?>">
                        <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                    </audio>

                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-box">
            <div class="text-center mb-4">
                <h5 class="fw-bold m-0" id="revTitle">Music Name</h5>
                <small class="text-muted">How was your listening experience?</small>
            </div>
            <form method="POST">
                <input type="hidden" name="music_id" id="revMusicId">
                <div class="star-rating">
                    <input type="radio" name="rating" value="5" id="r5" required><label for="r5">★</label>
                    <input type="radio" name="rating" value="4" id="r4"><label for="r4">★</label>
                    <input type="radio" name="rating" value="3" id="r3"><label for="r3">★</label>
                    <input type="radio" name="rating" value="2" id="r2"><label for="r2">★</label>
                    <input type="radio" name="rating" value="1" id="r1"><label for="r1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write your review..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-dark w-100" onclick="closeReview()">CANCEL</button>
                    <button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color:white;">POST NOW</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 MUSIC STUDIO &bull; SOUND SYSTEM</footer>

    <script>
        function togglePlay(btn) {
            const card = btn.closest('.album-card');
            const audio = card.querySelector('audio');
            document.querySelectorAll('audio').forEach(a => {
                if (a !== audio) {
                    a.pause();
                    a.closest('.album-card').classList.remove('playing');
                }
            });
            if (audio.paused) {
                audio.play();
                card.classList.add('playing');
                btn.querySelector('i').className = 'bi bi-pause-fill';
            } else {
                audio.pause();
                card.classList.remove('playing');
                btn.querySelector('i').className = 'bi bi-play-fill';
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

        document.getElementById('search').addEventListener('input', function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll('.album-card').forEach(card => {
                let txt = card.dataset.title + ' ' + card.dataset.artist;
                card.style.display = txt.includes(val) ? 'block' : 'none';
            });
        });
    </script>
</body>

</html>