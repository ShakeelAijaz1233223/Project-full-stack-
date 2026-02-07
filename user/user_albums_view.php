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
    <style>
        :root {
            --bg-dark: #080808;
            --card-bg: #121212;
            --accent: #ff0055;
            --accent-gradient: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #888888;
        }

        body {
            background-color: var(--bg-dark);
            color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        .studio-wrapper {
            width: 95%;
            margin: 0 auto;
            padding: 20px 0;
        }

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
            border-radius: 4px;
            padding: 6px 15px;
            width: 250px;
            font-size: 0.85rem;
        }

        .btn-back,
        .btn-delete,
        .btn-edit {
            background: #1a1a1a;
            border: 1px solid #222;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.3s ease;
        }

        .btn-back:hover,
        .btn-delete:hover,
        .btn-edit:hover {
            background: #222;
            border-color: var(--accent);
            color: #fff;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid transparent;
            border-radius: 10px;
            overflow: hidden;
            transition: 0.3s ease;
            text-align: center;
            padding: 10px;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            background: #1a1a1a;
            border-color: #333;
        }

        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            background: #000;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .play-overlay {
            position: absolute;
            width: 35px;
            height: 35px;
            background: var(--accent-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 10;
            border: none;
            color: white;
        }

        .card:hover .play-overlay {
            opacity: 1;
        }

        .title {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 1px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        /* Rating UI */
        .stars-row {
            color: #ffca08;
            font-size: 0.7rem;
            margin: 4px 0;
        }

        .btn-rev-pop {
            background: #000;
            color: #ccc;
            border: 1px solid #222;
            font-size: 0.65rem;
            width: 100%;
            padding: 4px;
            border-radius: 4px;
            margin-top: 5px;
            font-weight: 600;
        }

        .btn-rev-pop:hover {
            background: var(--accent);
            color: white;
        }

        /* Review Modal */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .review-modal {
            background: #111;
            width: 90%;
            max-width: 380px;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #222;
        }

        .star-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }

        .star-input input {
            display: none;
        }

        .star-input label {
            font-size: 2.5rem;
            color: #222;
            cursor: pointer;
        }

        .star-input label:hover,
        .star-input label:hover~label,
        .star-input input:checked~label {
            color: #ffca08;
        }

        .card-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 6px;
            z-index: 20;
        }

        footer {
            padding: 40px;
            text-align: center;
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
                <div class="card album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                    <div class="card-actions">
                        <a href="?delete=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                        <a href="edit_album.php?id=<?= $row['id']; ?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
                    </div>

                    <div class="media-wrapper">
                        <?php if (!empty($row['video'])): ?>
                            <video id="vid-<?= $row['id']; ?>" preload="metadata" playsinline muted loop poster="../admin/uploads/albums/<?= $row['cover']; ?>">
                                <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                            </video>
                            <button class="play-overlay" onclick="togglePlay('<?= $row['id']; ?>', this)">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        <?php else: ?>
                            <img src="../admin/uploads/albums/<?= $row['cover']; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                        <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>

                        <div class="stars-row">
                            <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                            <span class="ms-1 text-muted">(<?= $row['total_reviews'] ?>)</span>
                        </div>

                        <button class="btn-rev-pop" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">RATE ALBUM</button>

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
            <p class="text-center text-muted small mb-4">Leave a rating and comment</p>
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
                    <button type="submit" name="submit_review" class="btn btn-danger w-100">Post</button>
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

    // Fixed Media Toggle
    function togglePlay(id, btn) {
        const video = document.getElementById('vid-' + id);
        const audio = document.getElementById('aud-' + id);
        const icon = btn.querySelector('i');

        // Pause all other videos and audios
        document.querySelectorAll('video, audio').forEach(media => {
            if (media !== video && media !== audio) {
                media.pause();
            }
        });

        // Play/Pause logic
        if (video && video.paused) {
            video.muted = false; // allow sound
            video.play();
            if (audio) audio.play();
            icon.className = 'bi bi-pause-fill';
        } else if (video) {
            video.pause();
            if (audio) audio.pause();
            icon.className = 'bi bi-play-fill';
        }
    }
</script>

</body>

</html>