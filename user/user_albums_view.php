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

// 3. Fetch albums (Added Year column to show)
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

        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; margin: 0; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 25px 0; }

        .header-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 30px; }
        .search-box { background: #1f1f1f; border: 1px solid #333; color: var(--text-main); border-radius: 10px; padding: 8px 16px; width: 280px; transition: 0.3s; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .album-card { background: var(--card); border-radius: 22px; padding: 15px; border: 1px solid #2a2a2a; transition: 0.3s; position: relative; }
        .album-card:hover { transform: translateY(-5px); border-color: var(--accent); }

        .media-wrapper { position: relative; width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 15px; overflow: hidden; margin-bottom: 12px; }
        .media-wrapper img, .media-wrapper video { width: 100%; height: 100%; object-fit: cover; }
        
        .play-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50px; height: 50px; background: var(--accent-grad); border-radius: 50%; border: none; color: #fff; display: flex; align-items: center; justify-content: center; opacity: 0; cursor: pointer; z-index: 5; transition: 0.3s; }
        .album-card:hover .play-btn { opacity: 1; }

        .title { font-size: 1rem; font-weight: 700; margin: 0; color: #fff; }
        .meta-info { font-size: 0.8rem; color: var(--accent); margin-bottom: 5px; font-weight: 500; }
        .album-year { font-size: 0.75rem; color: #555; background: #222; padding: 2px 8px; border-radius: 5px; }

        /* Audio Player Bar inside card */
        .audio-container { background: #222; border-radius: 10px; padding: 8px; margin: 10px 0; display: flex; align-items: center; gap: 10px; }
        .audio-container audio { height: 25px; width: 100%; filter: invert(1); opacity: 0.7; }

        .stars-row { font-size: 0.78rem; color: #ffd700; margin-bottom: 12px; }
        .btn-rev-pop { background: var(--accent-grad); border: none; color: #fff; font-size: 0.8rem; font-weight: 600; padding: 10px; width: 100%; border-radius: 12px; transition: 0.3s; }

        #reviewOverlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(5px); z-index: 999; align-items: center; justify-content: center; }
        .review-modal { background: var(--card); padding: 30px; border-radius: 20px; width: 90%; max-width: 400px; border: 1px solid #333; }
        
        .star-input { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; }
        .star-input input { display: none; }
        .star-input label { font-size: 2.5rem; color: #333; cursor: pointer; }
        .star-input input:checked ~ label, .star-input label:hover, .star-input label:hover ~ label { color: #ffd700; }
        
        footer { text-align: center; padding: 50px; color: #444; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search by name or artist...">
            <a href="index.php" class="btn-back" style="text-decoration:none; color:white;"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        <?php while ($row = mysqli_fetch_assoc($albums)): 
            $avg = round($row['avg_rating'], 1);
        ?>
            <div class="album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                
                <div class="media-wrapper">
                    <?php if (!empty($row['video'])): ?>
                        <video id="vid-<?= $row['id']; ?>" loop muted playsinline poster="../admin/uploads/albums/<?= $row['cover']; ?>">
                            <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                        </video>
                        <button class="play-btn" onclick="toggleVideo('<?= $row['id']; ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    <?php else: ?>
                        <img src="../admin/uploads/albums/<?= $row['cover']; ?>" alt="Cover">
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="title"><?= htmlspecialchars($row['title']); ?></p>
                        <p class="meta-info"><?= htmlspecialchars($row['artist']); ?></p>
                    </div>
                    <span class="album-year"><?= htmlspecialchars($row['year']); ?></span>
                </div>

                <?php if (!empty($row['audio'])): ?>
                    <div class="audio-container">
                        <i class="bi bi-music-note-beamed text-accent" style="color:var(--accent)"></i>
                        <audio controls controlsList="nodownload">
                            <source src="../admin/uploads/albums/<?= $row['audio']; ?>" type="audio/mpeg">
                        </audio>
                    </div>
                <?php endif; ?>

                <div class="stars-row">
                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span class="ms-1 text-muted">(<?= $row['total_reviews'] ?>)</span>
                </div>

                <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                    RATE THIS ALBUM
                </button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-modal">
        <h5 class="text-center mb-1" id="popTitle">Album Name</h5>
        <p class="text-center text-muted small mb-4">How much do you like it?</p>
        <form method="POST">
            <input type="hidden" name="album_id" id="popId">
            <div class="star-input mb-4">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Tell us what you think..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                <button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color:white; border:none;">Post Review</button>
            </div>
        </form>
    </div>
</div>

<footer>&copy; 2026 ALBUMS STUDIO &bull; FULL MEDIA SYSTEM</footer>

<script>
    // Search function
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase().trim();
        document.querySelectorAll(".album-card").forEach(card => {
            let combined = card.dataset.title + " " + card.dataset.artist;
            card.style.display = combined.includes(val) ? "block" : "none";
        });
    });

    // Video Toggle Function
    function toggleVideo(id, btn) {
        const video = document.getElementById('vid-' + id);
        const icon = btn.querySelector('i');
        
        // Pause all other videos
        document.querySelectorAll('video').forEach(v => {
            if(v !== video) v.pause();
        });

        if (video.paused) {
            video.play();
            icon.className = 'bi bi-pause-fill';
        } else {
            video.pause();
            icon.className = 'bi bi-play-fill';
        }
    }

    // Review Modal Functions
    function popReview(id, title) {
        document.getElementById('popId').value = id;
        document.getElementById('popTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }
    function closePop() {
        document.getElementById('reviewOverlay').style.display = 'none';
    }
</script>

</body>
</html>