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

// 3. Fetch albums
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
            --bg-dark: #0f0f0f;
            --card-bg: #1a1a1a;
            --accent: #ff0055;
            --glass: rgba(255, 255, 255, 0.05);
            --text-main: #ffffff;
            --text-dim: #b3b3b3;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.02em;
        }

        .studio-wrapper {
            width: 92%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 0;
        }

        /* --- Header Styling --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .search-box {
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            width: 300px;
            transition: 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255,255,255,0.1);
        }

        /* --- New Card Design --- */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .album-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 16px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            border: 1px solid rgba(255,255,255,0.03);
            height: 100%;
        }

        .album-card:hover {
            transform: translateY(-10px);
            background: #252525;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border-color: rgba(255,0,85,0.3);
        }

        .media-wrapper {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
            margin-bottom: 15px;
        }

        /* Float Action Buttons (Edit/Delete) */
        .admin-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
            opacity: 0;
            transform: translateY(-5px);
            transition: 0.3s ease;
            z-index: 100;
        }

        .album-card:hover .admin-actions {
            opacity: 1;
            transform: translateY(0);
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-del:hover { background: #ff0055; color: white; }
        .btn-edit:hover { background: #007bff; color: white; }

        .title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .artist {
            font-size: 0.85rem;
            color: var(--text-dim);
            margin-bottom: 12px;
        }

        .rating-badge {
            background: rgba(255, 202, 8, 0.1);
            color: #ffca08;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
        }

        .btn-rate {
            margin-top: 15px;
            width: 100%;
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-rate:hover {
            background: var(--accent);
            color: white;
            box-shadow: 0 0 15px rgba(255,0,85,0.4);
        }

        /* Glassmorphism Modal */
        #reviewOverlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center; justify-content: center;
        }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h2>
        <div class="d-flex gap-3">
            <input type="text" id="search" class="search-box" placeholder="Search by title or artist...">
            <a href="index.php" class="btn btn-outline-light border-0"><i class="bi bi-house-door"></i></a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        <?php while ($row = mysqli_fetch_assoc($albums)): 
            $avg = round($row['avg_rating'], 1);
        ?>
            <div class="album-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                
                <div class="admin-actions">
                    <a href="edit_album.php?id=<?= $row['id'] ?>" class="action-btn btn-edit"><i class="bi bi-pencil-square"></i></a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this album?')" class="action-btn btn-del"><i class="bi bi-trash3"></i></a>
                </div>

                <div class="media-wrapper">
                    <?php if (!empty($row['video'])): ?>
                        <video id="vid-<?= $row['id']; ?>" preload="metadata" poster="../admin/uploads/albums/<?= $row['cover']; ?>" style="width:100%; height:100%; object-fit:cover;">
                            <source src="../admin/uploads/albums/<?= $row['video']; ?>" type="video/mp4">
                        </video>
                    <?php else: ?>
                        <img src="../admin/uploads/albums/<?= $row['cover']; ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php endif; ?>
                </div>

                <div class="card-content">
                    <div class="title"><?= htmlspecialchars($row['title']); ?></div>
                    <div class="artist"><?= htmlspecialchars($row['artist']); ?></div>
                    
                    <div class="d-flex justify-content-between align-items:center;">
                        <div class="rating-badge">
                            <i class="bi bi-star-fill"></i> <?= $avg > 0 ? $avg : 'New' ?>
                        </div>
                        <small class="text-muted"><?= $row['total_reviews'] ?> reviews</small>
                    </div>

                    <button class="btn-rate" onclick="popReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">Rate Album</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="card bg-dark text-white border-secondary p-4" style="width: 350px; border-radius:20px;">
        <h5 id="popTitle" class="text-center fw-bold">Album Name</h5>
        <form method="POST">
            <input type="hidden" name="album_id" id="popId">
            <div class="mb-3 text-center">
                <select name="rating" class="form-select bg-black text-white border-secondary" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                    <option value="3">⭐⭐⭐ (3/5)</option>
                    <option value="2">⭐⭐ (2/5)</option>
                    <option value="1">⭐ (1/5)</option>
                </select>
            </div>
            <textarea name="comment" class="form-control bg-black text-white border-secondary mb-3" placeholder="Write feedback..." required></textarea>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closePop()">Cancel</button>
                <button type="submit" name="submit_review" class="btn btn-danger w-100">Post</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Search logic
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase().trim();
        document.querySelectorAll(".album-card").forEach(card => {
            let text = card.dataset.title + card.dataset.artist;
            card.style.display = text.includes(val) ? "block" : "none";
        });
    });

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