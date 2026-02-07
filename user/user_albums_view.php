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
    --bg: #080808;
    --card: #111;
    --accent: #ff0055;
    --accent-grad: linear-gradient(45deg, #ff0055, #ff5e00);
    --text-main: #fff;
    --text-muted: #aaa;
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
    padding: 20px 0;
}

/* --- Header --- */
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
    color: white;
    border-radius: 6px;
    padding: 6px 15px;
    width: 250px;
}

/* --- Album Grid & Cards --- */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
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
    border-color: var(--accent);
    transform: translateY(-5px);
}

/* --- Media Wrapper --- */
.media-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}
.media-wrapper img,
.media-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.album-card:hover .media-wrapper img,
.album-card:hover .media-wrapper video {
    transform: scale(1.05);
}

/* --- Play Button --- */
.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 35px;
    height: 35px;
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
.media-wrapper:hover .custom-controls { opacity: 1; }
.custom-controls button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
}
.custom-controls input[type="range"] {
    flex: 1;
    margin: 0 5px;
    accent-color: var(--accent);
}

/* --- Titles & Artists --- */
.title {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 5px 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.artist {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 8px;
}

/* --- Stars --- */
.stars-row {
    color: #ffca08;
    font-size: 0.75rem;
    margin-bottom: 10px;
}

/* --- Review Button --- */
.btn-rev-pop {
    background: var(--accent) !important;
    border: none;
    color: #fff;
    font-size: 0.75rem;
    padding: 6px;
    width: 100%;
    border-radius: 6px;
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
    background: rgba(0,0,0,0.9);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.review-modal {
    background: var(--card);
    width: 90%;
    max-width: 380px;
    padding: 25px;
    border-radius: 15px;
    border: 1px solid #222;
}
.star-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 8px;
    margin-bottom: 15px;
}
.star-input input { display: none; }
.star-input label {
    font-size: 2rem;
    color: #222;
    cursor: pointer;
}
.star-input label:hover,
.star-input label:hover~label,
.star-input input:checked~label { color: #ffca08; }

/* --- Footer --- */
footer {
    text-align: center;
    padding: 30px;
    font-size: 0.7rem;
    color: #444;
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
