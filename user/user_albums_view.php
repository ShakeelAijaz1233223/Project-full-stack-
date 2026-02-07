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

// 2. Handle Review Submission (Targeting album_reviews table)
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #050505;
            --card-bg: rgba(255, 255, 255, 0.03);
            --accent: #ff0055;
            --primary-gradient: linear-gradient(135deg, #ff0055, #7000ff);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-dim: #a0a0a0;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            background-image: radial-gradient(circle at 50% -20%, #1a1a2e 0%, #050505 80%);
            min-height: 100vh;
        }

        .studio-wrapper {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 0;
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
        }

        .search-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            width: 300px;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(255, 0, 85, 0.2);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            font-weight: 600;
        }

        .btn-back:hover {
            background: #fff;
            color: #000;
        }

        /* Grid Layout */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
        }

        /* Album Card Styling */
        .album-card {
            background: var(--card-bg) !important;
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border) !important;
            border-radius: 20px !important;
            padding: 15px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .album-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(255, 0, 85, 0.5) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .media-wrapper img, .media-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s ease;
        }

        .album-card:hover .media-wrapper img, 
        .album-card:hover .media-wrapper video {
            transform: scale(1.1);
        }

        /* Card Text */
        .card-body {
            padding: 15px 5px 0 5px !important;
            text-align: left !important;
        }

        .title {
            font-weight: 800;
            font-size: 1rem;
            margin-bottom: 2px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist {
            font-size: 0.85rem;
            color: var(--text-dim);
            margin-bottom: 12px;
            font-weight: 500;
        }

        /* Rating UI */
        .stars-row {
            color: #ffca08;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 15px;
        }

        /* Premium Button */
        .btn-rev-pop {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
            font-size: 0.75rem !important;
            width: 100%;
            padding: 12px !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
        }

        .btn-rev-pop:hover {
            filter: brightness(1.2);
            box-shadow: 0 8px 25px rgba(255, 0, 85, 0.5);
            transform: scale(1.02);
        }

        /* Custom Video Controls */
        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            padding: 10px;
            gap: 10px;
            opacity: 0;
            transition: 0.3s;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .custom-controls button {
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
        }

        /* Modal Styling */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(15px);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .review-modal {
            background: #111;
            width: 90%;
            max-width: 400px;
            padding: 40px;
            border-radius: 30px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .star-input label {
            font-size: 2.5rem;
            color: #222;
            cursor: pointer;
            transition: 0.2s;
        }

        footer {
            padding: 60px;
            text-align: center;
            font-size: 0.8rem;
            color: #444;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="studio-wrapper">
        <div class="header-section">
            <h4 class="m-0 fw-bold" style="letter-spacing: -1px;">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
            <div class="d-flex align-items-center gap-3">
                <input type="text" id="search" class="search-box" placeholder="Search artists, albums...">
                <a href="index.php" class="btn-back"><i class="bi bi-house-door"></i> Dashboard</a>
            </div>
        </div>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success border-0 bg-success text-white py-3 rounded-4 mb-4" style="font-size:0.9rem;">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="grid" id="albumGrid">
            <?php while ($row = mysqli_fetch_assoc($albums)):
                $avg = round($row['avg_rating'], 1);
            ?>
                <div class="card album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    
                    <div class="media-wrapper">
                        <?php if (!empty($row['video'])): ?>
                            <video id="vid-<?= $row['id']; ?>" preload="metadata" poster="../admin/uploads/albums/<?= $row['cover']; ?>">
                                <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                            </video>

                            <div class="custom-controls" id="controls-<?= $row['id']; ?>">
                                <button class="play-btn"><i class="bi bi-play-fill"></i></button>
                                <input type="range" class="progress form-range" min="0" max="100" value="0" style="height: 4px;">
                                <button class="mute-btn"><i class="bi bi-volume-up"></i></button>
                                <button class="fullscreen-btn"><i class="bi bi-arrows-fullscreen"></i></button>
                            </div>
                        <?php else: ?>
                            <img src="../admin/uploads/albums/<?= $row['cover']; ?>">
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                        <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>

                        <div class="stars-row">
                            <span style="color: #ffca08;">
                                <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                            </span>
                            <span class="ms-1 text-muted" style="font-size: 0.7rem;">(<?= $row['total_reviews'] ?>)</span>
                        </div>

                        <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                            <i class="bi bi-star-fill me-2"></i>Rate Album
                        </button>

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
            <h4 class="text-center fw-bold mb-1" id="popTitle">Album Name</h4>
            <p class="text-center text-muted small mb-4">How would you rate this record?</p>
            <form method="POST">
                <input type="hidden" name="album_id" id="popId">
                <div class="star-input d-flex flex-row-reverse justify-content-center gap-2 mb-4">
                    <input type="radio" name="rating" value="5" id="s5" class="d-none" required><label for="s5">★</label>
                    <input type="radio" name="rating" value="4" id="s4" class="d-none"><label for="s4">★</label>
                    <input type="radio" name="rating" value="3" id="s3" class="d-none"><label for="s3">★</label>
                    <input type="radio" name="rating" value="2" id="s2" class="d-none"><label for="s2">★</label>
                    <input type="radio" name="rating" value="1" id="s1" class="d-none"><label for="s1">★</label>
                </div>
                <textarea name="comment" class="form-control bg-dark text-white border-0 rounded-4 p-3 mb-4" rows="3" placeholder="Share your thoughts..." required></textarea>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-dark w-100 rounded-3 py-2" onclick="closePop()">Cancel</button>
                    <button type="submit" name="submit_review" class="btn btn-rev-pop w-100 py-2">Submit</button>
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
                let text = card.dataset.title + card.dataset.artist;
                card.style.display = text.includes(val) ? "block" : "none";
            });
        });

        // Review Modal Functions
        function popReview(id, title) {
            document.getElementById('popId').value = id;
            document.getElementById('popTitle').innerText = title;
            document.getElementById('reviewOverlay').style.display = 'flex';
        }

        function closePop() {
            document.getElementById('reviewOverlay').style.display = 'none';
        }

        // Single video player logic
        document.querySelectorAll('.media-wrapper').forEach(wrapper => {
            const video = wrapper.querySelector('video');
            if (!video) return;

            const playBtn = wrapper.querySelector('.play-btn');
            const progress = wrapper.querySelector('.progress');
            const muteBtn = wrapper.querySelector('.mute-btn');
            const fullscreenBtn = wrapper.querySelector('.fullscreen-btn');

            playBtn.addEventListener('click', () => {
                document.querySelectorAll('video').forEach(v => {
                    if (v !== video) v.pause();
                });

                if (video.paused) {
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

            muteBtn.addEventListener('click', () => {
                video.muted = !video.muted;
                muteBtn.innerHTML = video.muted ? '<i class="bi bi-volume-mute"></i>' : '<i class="bi bi-volume-up"></i>';
            });

            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    wrapper.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            });

            video.addEventListener('ended', () => {
                playBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
            });
        });
    </script>
</body>
</html>