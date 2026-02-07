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

// 2. Fetch Music with Average Ratings
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

/* --- Header --- */
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 15px;
}
.search-box {
    background: #151515;
    border: 1px solid #333;
    color: white;
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

/* --- Grid & Cards --- */
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
    text-align: center;
}
.album-card:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
}

/* --- Disc / Media --- */
.disc-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}
.disc-wrapper img, .disc-wrapper video, .disc-wrapper audio {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.album-card:hover .disc-wrapper img,
.album-card:hover .disc-wrapper video {
    transform: scale(1.05);
}

/* --- Play Button --- */
.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    background: var(--accent-grad);
    border-radius: 50%;
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    cursor: pointer;
    z-index: 5;
    transition: 0.3s;
}
.album-card:hover .play-btn { opacity: 1; }

/* --- Custom Controls --- */
.custom-controls {
    position: absolute;
    bottom: 5px;
    left: 0;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 0 0 8px 8px;
}
.album-card:hover .custom-controls { opacity: 1; }
.custom-controls button { background: none; border: none; color: white; cursor: pointer; font-size: 1rem; }
.custom-controls input[type="range"] { flex: 1; margin: 0 5px; accent-color: var(--accent); }

/* --- Title & Stars --- */
.title { font-weight: 600; font-size: 0.85rem; margin: 5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.artist { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 5px; }
.stars-display { color: #ffca08; font-size: 0.75rem; margin-bottom: 8px; }

/* --- Review Button --- */
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
.rev-btn:hover { background: var(--accent); }

/* --- Review Overlay --- */
#reviewOverlay {
    display: none;
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.9);
    backdrop-filter: blur(5px);
    z-index: 9999;
    align-items:center; justify-content:center;
}
.review-box {
    background: #151515;
    width: 90%;
    max-width: 400px;
    padding: 30px;
    border-radius: 20px;
    border: 1px solid #333;
}

/* --- Star Rating --- */
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 8px;
    margin-bottom: 15px;
}
.star-rating input { display: none; }
.star-rating label { font-size: 2.5rem; color: #222; cursor: pointer; transition: 0.2s; }
.star-rating label:hover,
.star-rating label:hover~label,
.star-rating input:checked~label { color: #ffca08; }

/* --- Footer --- */
footer { text-align:center; padding:40px; font-size:0.7rem; color:#444; }

audio { width: 100%; display:none; } /* hide default audio player */
</style>

</head>

<body>

    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold">MUSIC<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search music...">
                <a href="javascript:history.back()" class="btn btn-dark btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <div class="grid" id="musicGrid">
            <?php while ($row = mysqli_fetch_assoc($music)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="card album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    <div class="disc-wrapper">
                        <i class="bi bi-disc-fill"></i>
                        <button class="play-trigger" onclick="togglePlay(this)"><i class="bi bi-play-fill"></i></button>
                    </div>
                    <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                    <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
                    <div class="stars-row">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                        <span class="text-muted ms-1">(<?= $row['total_reviews']; ?>)</span>
                    </div>
                    <button class="btn-rev-pop" onclick="openReview('<?= $row['id']; ?>','<?= addslashes($row['title']); ?>')">REVIEW</button>
                    <audio>
                        <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-modal">
            <h5 class="text-center mb-2" id="popTitle">Rate Music</h5>
            <form method="POST">
                <input type="hidden" name="music_id" id="popId">
                <div class="star-input">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write feedback..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                    <button type="submit" name="submit_review" class="btn btn-danger w-100">Post</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 MUSIC STUDIO &bull; SOUND SYSTEM</footer>

    <script>
        function openReview(id, title) {
            document.getElementById('popId').value = id;
            document.getElementById('popTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closePop() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }

        // Play Music
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

        // Search
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase();
            document.querySelectorAll(".album-card").forEach(card => {
                let txt = card.dataset.title + " " + card.dataset.artist;
                card.style.display = txt.includes(val) ? "block" : "none";
            });
        });
    </script>
</body>

</html>