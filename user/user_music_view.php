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
    --bg: #0d0d0d;
    --card: #1b1b1b;
    --accent: #ff3366;
    --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
    --text-main: #f5f5f5;
    --text-muted: #999;
    --shadow: rgba(0,0,0,0.6);
}

/* --- Body & Wrapper --- */
body {
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
    margin: 0;
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
    transition: all 0.3s;
}
.search-box:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 8px var(--accent);
}
.btn-back {
    background: #222;
    border: none;
    color: var(--text-main);
    padding: 7px 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: 0.3s;
}
.btn-back:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Grid & Music Cards --- */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 25px;
}
.album-card {
    background: var(--card);
    border-radius: 20px;
    overflow: hidden;
    padding: 12px;
    position: relative;
    border: 1px solid #2a2a2a;
    box-shadow: 0 4px 15px var(--shadow);
    transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
}
.album-card:hover {
    transform: translateY(-6px);
    border-color: var(--accent);
    box-shadow: 0 8px 20px var(--shadow);
}

/* --- Disc / Media Wrapper --- */
.disc-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* --- Play Button --- */
.play-btn {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    border-radius: 50%;
    border: none;
    background: var(--accent-grad);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    box-shadow: 0 0 20px var(--accent);
    opacity: 0;
    transition: transform 0.3s, opacity 0.3s;
}
.album-card:hover .play-btn {
    opacity: 1;
}
.play-btn:hover {
    transform: translate(-50%, -50%) scale(1.2);
}

/* --- Custom Controls --- */
.custom-controls {
    position: absolute;
    bottom: 6px;
    left: 6px;
    right: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 12px;
    background: rgba(30, 30, 30, 0.75);
    backdrop-filter: blur(6px);
    opacity: 0;
    border-radius: 0 0 12px 12px;
    transition: opacity 0.3s;
}
.disc-wrapper:hover .custom-controls {
    opacity: 1;
}
.custom-controls button {
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 1.2rem;
    transition: 0.25s;
}
.custom-controls button:hover {
    color: var(--accent);
    transform: scale(1.25);
}
.custom-controls input[type="range"] {
    flex: 1;
    margin: 0 6px;
    accent-color: var(--accent);
    background: rgba(255, 255, 255, 0.12);
    border-radius: 4px;
}

/* --- Titles & Artists --- */
.title {
    font-weight: 600;
    font-size: 0.88rem;
    margin: 6px 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.artist {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-bottom: 8px;
}

/* --- Stars --- */
.stars-display {
    font-size: 0.78rem;
    color: #ffd700;
    margin-bottom: 10px;
    text-align: center;
}

/* --- Review Button --- */
.rev-btn {
    background: #222;
    color: #fff;
    border: none;
    font-size: 0.78rem;
    width: 100%;
    padding: 8px;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s;
}
.rev-btn:hover {
    background: var(--accent);
}

/* --- Video Download Button --- */
.download-btn {
    display: block;
    text-align: center;
    font-size: 0.85rem;
    padding: 7px;
    border-radius: 10px;
    transition: 0.3s;
}
.download-btn:hover {
    background: var(--accent);
    color: #fff;
}

/* --- Review Overlay --- */
#reviewOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.95);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.review-box {
    background: var(--card);
    width: 90%;
    max-width: 420px;
    padding: 35px;
    border-radius: 22px;
    border: 1px solid #2a2a2a;
}
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 10px;
    margin-bottom: 18px;
}
.star-rating input { display: none; }
.star-rating label {
    font-size: 2.2rem;
    color: #333;
    cursor: pointer;
    transition: 0.3s;
}
.star-rating label:hover,
.star-rating label:hover~label,
.star-rating input:checked~label {
    color: #ffd700;
}

/* --- Footer --- */
footer {
    text-align: center;
    padding: 50px 0;
    font-size: 0.75rem;
    color: #555;
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
                        <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                        <div class="custom-controls">
                            <input type="range" class="progress" min="0" max="100" value="0">
                            <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                        </div>
                    </div>
                    <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                    <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
                    <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="text-white opacity-50 ms-1">(<?= $row['total_reviews']; ?>)</span>
                    </div>
                    <button class="rev-btn" onclick="openReview('<?= $row['id']; ?>','<?= addslashes($row['title']); ?>')">REVIEW</button>

                    <!-- Video Download Button -->
                    <?php if (!empty($row['video'])): ?>
                        <a href="../admin/uploads/music/<?= $row['video']; ?>" download class="btn w-100 mt-2 download-btn" style="background:#444; color:white; font-size:0.8rem; border-radius:6px; text-align:center;">
                            <i class="bi bi-download"></i> Download Video
                        </a>
                    <?php endif; ?>

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
        const audios = document.querySelectorAll('audio');
        const albumCards = document.querySelectorAll('.album-card');

        albumCards.forEach(card => {
            const btn = card.querySelector('.play-btn');
            const audio = card.querySelector('audio');
            const progress = card.querySelector('.progress');
            const muteBtn = card.querySelector('.mute-btn');

            btn.addEventListener('click', () => {
                audios.forEach(a => {
                    if (a !== audio) {
                        a.pause();
                        a.closest('.album-card').classList.remove('playing');
                        a.closest('.album-card').querySelector('.play-btn i').className = 'bi bi-play-fill';
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

                audio.addEventListener('timeupdate', () => {
                    progress.value = (audio.currentTime / audio.duration) * 100;
                });
            });

            progress.addEventListener('input', () => {
                audio.currentTime = (progress.value / 100) * audio.duration;
            });

            muteBtn.addEventListener('click', () => {
                audio.muted = !audio.muted;
                muteBtn.innerHTML = audio.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
            });
        });

        document.getElementById('search').addEventListener('input', function() {
            const val = this.value.toLowerCase();
            albumCards.forEach(card => {
                let txt = card.dataset.title + ' ' + card.dataset.artist;
                card.style.display = txt.includes(val) ? 'block' : 'none';
            });
        });

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