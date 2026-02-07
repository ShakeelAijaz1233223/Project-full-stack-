<?php
session_start();
include "../config/db.php";

// 1. Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $album = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM albums WHERE id=$delete_id"));
    if ($album) {
        @unlink("../admin/uploads/albums/" . $album['cover']);
        @unlink("../admin/uploads/albums/" . $album['audio']);
        @unlink("../admin/uploads/albums/" . $album['video']);
        mysqli_query($conn, "DELETE FROM albums WHERE id=$delete_id");
        $msg = "Album deleted successfully!";
    }
}

// 2. Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $album_id = $_POST['album_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO album_reviews (album_id, rating, comment) VALUES ('$album_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// 3. Fetch albums with Average Ratings
$query = "SELECT albums.*, 
          (SELECT AVG(rating) FROM album_reviews WHERE album_reviews.album_id = albums.id) as avg_rating,
          (SELECT COUNT(*) FROM album_reviews WHERE album_reviews.album_id = albums.id) as total_reviews
          FROM albums ORDER BY created_at DESC";
$albums = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Albums Studio | Pro Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

/* --- Album Grid & Cards --- */
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

/* --- Media Wrapper --- */
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 12px;
}
.media-wrapper img,
.media-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 15px;
    transition: transform 0.5s ease;
}
.album-card:hover .media-wrapper img,
.album-card:hover .media-wrapper video {
    transform: scale(1.07);
}

/* --- Play Button --- */
.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 45px;
    height: 45px;
    background: var(--accent-grad);
    border-radius: 50%;
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    cursor: pointer;
    z-index: 5;
    transition: 0.3s;
}
.album-card:hover .play-btn {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1.1);
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
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.media-wrapper:hover .custom-controls {
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
.custom-controls input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--accent);
    border: 2px solid #1b1b1b;
    transition: transform 0.2s;
}
.custom-controls input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.3);
}

/* --- Titles & Artists --- */
.title {
    font-size: 0.88rem;
    font-weight: 600;
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
.stars-row {
    font-size: 0.78rem;
    color: #ffd700;
    margin-bottom: 10px;
}

/* --- Review Button --- */
.btn-rev-pop {
    background: var(--accent) !important;
    border: none;
    color: #fff;
    font-size: 0.78rem;
    padding: 7px;
    width: 100%;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s;
}
.btn-rev-pop:hover {
    filter: brightness(1.2);
}

/* --- Review Overlay --- */
#reviewOverlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.95);
    backdrop-filter: blur(6px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.review-modal {
    background: var(--card);
    width: 90%;
    max-width: 420px;
    padding: 35px;
    border-radius: 22px;
    border: 1px solid #2a2a2a;
}
.star-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 10px;
    margin-bottom: 18px;
}
.star-input input { display: none; }
.star-input label {
    font-size: 2.2rem;
    color: #333;
    cursor: pointer;
    transition: 0.3s;
}
.star-input label:hover,
.star-input label:hover~label,
.star-input input:checked~label { color: #ffd700; }

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
            <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="search" class="search-box" placeholder="Search albums...">
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success py-2" style="font-size:0.8rem;"><?= $msg ?></div>
        <?php endif; ?>

        <div class="grid" id="albumGrid">
            <?php while ($row = mysqli_fetch_assoc($albums)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    <div class="media-wrapper">
                        <?php if (!empty($row['video'])): ?>
                            <video id="vid-<?= $row['id']; ?>" preload="metadata" poster="../admin/uploads/albums/<?= $row['cover']; ?>">
                                <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                            </video>
                            <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                            <div class="custom-controls">
                                <input type="range" class="progress" min="0" max="100" value="0">
                                <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                                <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                        <?php else: ?>
                            <img src="../admin/uploads/albums/<?= $row['cover']; ?>" class="album-cover">
                        <?php endif; ?>
                    </div>
                     <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="title"><?= htmlspecialchars($row['title']); ?></p>
                        <p class="meta-info"><?= htmlspecialchars($row['artist']); ?></p>
                    </div>
                    <span class="album-year"><?= htmlspecialchars($row['year']); ?></span>
                </div>

                    <div class="card-body">
                        <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                        <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>

                        <div class="stars-row">
                            <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                            <span class="ms-2 text-muted" style="font-size: 0.7rem;">(<?= $row['total_reviews'] ?>)</span>
                        </div>

                        <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">Rate Album</button>

                        <?php if (!empty($row['audio'])): ?>
                            <audio id="aud-<?= $row['id']; ?>">
                                <source src="../admin/uploads/albums/<?= $row['audio']; ?>" type="audio/mpeg">
                            </audio>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="reviewOverlay">
        <div class="review-modal">
            <h5 class="text-center mb-1" id="popTitle">Album Name</h5>
            <p class="text-center text-muted small mb-4">Leave your rating</p>
            <form method="POST">
                <input type="hidden" name="album_id" id="popId">
                <div class="star-input mb-4">
                    <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write feedback..." required></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                    <button type="submit" name="submit_review" class="btn btn-danger w-100" style="background: var(--accent);">Post</button>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 ALBUMS STUDIO &bull; SOUND SYSTEM</footer>

    <script>
        // Search
        document.getElementById("search").addEventListener("input", function() {
            let val = this.value.toLowerCase().trim();
            document.querySelectorAll(".album-card").forEach(card => {
                let text = card.dataset.title + " " + card.dataset.artist;
                card.style.display = text.includes(val) ? "block" : "none";
            });
        });

        // Modals
        function popReview(id, title) {
            document.getElementById('popId').value = id;
            document.getElementById('popTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closePop() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }

        // Video Player logic
        document.querySelectorAll('.media-wrapper').forEach(wrapper => {
            const video = wrapper.querySelector('video');
            if (!video) return;

            const playBtn = wrapper.querySelector('.play-btn');
            const progress = wrapper.querySelector('.progress');
            const muteBtn = wrapper.querySelector('.mute-btn');
            const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');

            playBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (video.paused) {
                    document.querySelectorAll('video').forEach(v => v.pause());
                    video.play();
                    playBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
                } else {
                    video.pause();
                    playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
                }
            });

            video.addEventListener('timeupdate', () => {
                progress.value = (video.currentTime / video.duration) * 100;
            });

            progress.addEventListener('input', () => {
                video.currentTime = (progress.value / 100) * video.duration;
            });

            muteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                video.muted = !video.muted;
                muteBtn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
            });

            fullscreenBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (video.requestFullscreen) video.requestFullscreen();
            });
        });
    </script>
</body>

</html>