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
            --primary-gradient: linear-gradient(135deg, #ff0055, #7000ff);
            --bg-dark: #080808;
            --card-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-dim: #a0a0a0;
            --accent: #ff0055;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 20px 0;
        }

        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 15px;
        }

        .search-box {
            background: #1a1a1a;
            border: 1px solid #222;
            color: white;
            border-radius: 8px;
            padding: 8px 15px;
            width: 250px;
            font-size: 0.85rem;
        }

        /* New Album Card Styling */
      /* Grid layout */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Bigger cards */
    gap: 30px; /* More spacing */
    padding: 20px;
}

/* Album card styling */
.album-card {
    background: rgba(255, 255, 255, 0.05) !important; /* Glassy effect */
    backdrop-filter: blur(15px); /* Stronger blur */
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 25px !important; /* Bigger round corners */
    padding: 20px;
    transition: transform 0.5s ease, box-shadow 0.5s ease, border-color 0.5s ease, background 0.5s ease;
    position: relative;
    overflow: hidden;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

/* Hover effect */
.album-card:hover {
    transform: translateY(-15px) scale(1.05);
    background: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 0, 85, 0.6) !important;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
}

/* Media wrapper (image/video container) */
.media-wrapper {
    border-radius: 15px !important;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
    position: relative;
    aspect-ratio: 16/9; /* Wider video look */
    overflow: hidden;
    background: #000;
    margin-bottom: 15px;
}

/* Media inside card */
.media-wrapper img,
.media-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

/* Hover zoom for media */
.album-card:hover .media-wrapper img,
.album-card:hover .media-wrapper video {
    transform: scale(1.15); /* Bigger zoom on hover */
}

/* Album title & info */
.album-card h3 {
    color: #fff;
    font-size: 1.7rem; /* Bigger font */
    margin: 10px 0 5px;
}

.album-card p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.05rem;
}

/* Play button centered */
.album-card .play-btn {
    position: relative;
  padding-bottom: 100px;
    transform: translate(-50%, -50%);
    background: rgba(255, 0, 85, 0.3);
    border: none;
    padding: 16px 20px; /* Slightly bigger */
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    transition: transform 0.3s ease, background 0.3s ease;
}

.album-card .play-btn:hover {
    background: rgba(255, 0, 85, 0.6);
    /* transform: scale(1.3); Pop effect */
}


        /* Video Controls Overlay */
        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.6) !important;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            padding: 5px 10px;
            gap: 8px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 5;
        }

        .media-wrapper:hover .custom-controls {
            opacity: 1;
        }

        .custom-controls button {
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
        }

        .custom-controls input[type="range"] {
            flex: 1;
            accent-color: var(--accent);
        }

        /* Text Styling */
        .card-body {
            padding: 15px 5px 5px 5px !important;
            text-align: left !important;
        }

        .title {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: var(--text-main);
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist {
            font-size: 0.85rem !important;
            color: var(--text-dim) !important;
            font-weight: 500;
            margin-bottom: 10px;
        }

        /* Rating Stars */
        .stars-row {
            color: #ffca08;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 3px;
            margin-bottom: 15px;
        }

        /* Button Styling */
        .btn-rev-pop {
            background: var(--primary-gradient) !important;
            border: none !important;
            border-radius: 12px !important;
            color: white !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            padding: 10px !important;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 85, 0.3);
        }

        .btn-rev-pop:hover {
            box-shadow: 0 6px 20px rgba(255, 0, 85, 0.5);
            filter: brightness(1.2);
        }

        /* Modals & Other Components */
        .btn-back {
            background: #1a1a1a;
            border: 1px solid #222;
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center; justify-content: center;
        }

        .review-modal {
            background: #111;
            width: 90%; max-width: 380px;
            padding: 30px; border-radius: 20px;
            border: 1px solid var(--glass-border);
        }

        .star-input {
            display: flex; flex-direction: row-reverse;
            justify-content: center; gap: 10px;
        }

        .star-input input { display: none; }
        .star-input label { font-size: 2.5rem; color: #222; cursor: pointer; }
        .star-input label:hover, .star-input label:hover~label, .star-input input:checked~label { color: #ffca08; }

        footer { padding: 40px; text-align: center; font-size: 0.7rem; color: #444; }
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
                        <img src="../admin/uploads/albums/<?= $row['cover']; ?>">
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
    function closePop() { document.getElementById('reviewOverlay').style.display = 'none'; }

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