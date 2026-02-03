<?php
session_start();
include "../config/db.php";

// Handle Delete
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

$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albums Studio | 3-Line Pro</title>
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
body { background-color: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; margin: 0; }
.studio-wrapper { width: 95%; margin: 0 auto; padding: 20px 0; }
.header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #1a1a1a; padding-bottom: 15px; }

/* THREE LINE GRID LOGIC */
.grid { 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); /* Force 3 Columns */
    gap: 20px; 
}

@media (max-width: 992px) { .grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }

.card { background: var(--card-bg); border: 1px solid #1a1a1a; border-radius: 12px; overflow: hidden; transition: 0.3s ease; padding: 12px; position: relative; }
.card:hover { transform: translateY(-5px); border-color: #444; }

.media-wrapper { position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 8px; margin-bottom: 10px; overflow: hidden; }
video { width: 100%; height: 100%; object-fit: cover; }

.play-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 45px; height: 45px; background: var(--accent-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; transition: 0.3s; z-index: 10; border: none; color: white; }
.card:hover .play-overlay { opacity: 1; }

.title { font-weight: 600; font-size: 1rem; color: #fff; margin-top: 5px; }
.artist { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 10px; }

/* Review Section Styling */
.review-form input, .review-form textarea, .review-form select { 
    background: #000 !important; border: 1px solid #222 !important; color: #fff !important; font-size: 0.75rem !important; 
}
.rating-display { color: #ffcc00; font-size: 0.85rem; margin-bottom: 8px; }
.btn-post { background: var(--accent); color: white; border: none; font-size: 0.75rem; width: 100%; padding: 5px; border-radius: 4px; margin-top: 5px; transition: 0.3s; }
.btn-post:hover { background: #ff5e00; }

.card-actions { position: absolute; top: 15px; right: 15px; display: flex; gap: 8px; z-index: 20; }
.btn-action { background: rgba(0,0,0,0.6); border: 1px solid #333; color: #fff; padding: 5px 8px; border-radius: 4px; font-size: 0.8rem; }
</style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">ALBUMS<span style="color: var(--accent);">STUDIO</span></h4>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="search" class="search-box bg-dark text-white border-secondary px-2 py-1" placeholder="Search albums...">
            <a href="index.php" class="btn btn-sm btn-outline-light">Back</a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        <?php while ($row = mysqli_fetch_assoc($albums)): 
            $id = $row['id'];
            // Get Ratings
            $rat_res = mysqli_query($conn, "SELECT AVG(rating) as avg_r, COUNT(id) as total FROM album_reviews WHERE album_id=$id");
            $rat_data = mysqli_fetch_assoc($rat_res);
            $avg = round($rat_data['avg_r'], 1);
        ?>
        <div class="card album-card" data-title="<?= strtolower($row['title']); ?>">
            <div class="card-actions">
                <a href="?delete=<?= $id ?>" class="btn-action text-danger" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                <a href="edit_album.php?id=<?= $id ?>" class="btn-action text-primary"><i class="bi bi-pencil"></i></a>
            </div>

            <div class="media-wrapper">
                <?php if(!empty($row['video'])): ?>
                    <video id="vid-<?= $id ?>" preload="metadata" poster="../admin/uploads/albums/<?= $row['cover'] ?>">
                        <source src="../admin/uploads/albums/<?= $row['video'] ?>" type="video/mp4">
                    </video>
                    <button class="play-overlay" onclick="togglePlay('<?= $id ?>', this)"><i class="bi bi-play-fill"></i></button>
                <?php else: ?>
                    <img src="../admin/uploads/albums/<?= $row['cover'] ?>" class="w-100 h-100" style="object-fit:cover;">
                <?php endif; ?>
            </div>

            <div class="card-body p-1">
                <div class="title text-truncate"><?= htmlspecialchars($row['title']) ?></div>
                <div class="artist text-truncate"><?= htmlspecialchars($row['artist']) ?></div>
                
                <div class="rating-display">
                    <?= ($rat_data['total'] > 0) ? "⭐ $avg (".$rat_data['total']." reviews)" : "No reviews yet"; ?>
                </div>

                <form action="submit_review.php" method="POST" class="review-form mt-2">
                    <input type="hidden" name="album_id" value="<?= $id ?>">
                    <div class="d-flex gap-1 mb-1">
                        <select name="rating" class="form-select form-select-sm shadow-none">
                            <option value="5">5★</option><option value="4">4★</option><option value="3">3★</option>
                        </select>
                        <input type="text" name="user_name" class="form-control form-control-sm shadow-none" placeholder="Name" required>
                    </div>
                    <textarea name="review_text" class="form-control form-control-sm shadow-none" placeholder="Write a review..." rows="1" required></textarea>
                    <button type="submit" name="add_review" class="btn-post">SUBMIT REVIEW</button>
                </form>

                <?php if(!empty($row['audio'])): ?>
                    <audio id="aud-<?= $id ?>">
                        <source src="../admin/uploads/albums/<?= $row['audio'] ?>" type="audio/mpeg">
                    </audio>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function togglePlay(id, btn){
    const audio = document.getElementById('aud-'+id);
    const video = document.getElementById('vid-'+id);
    const icon = btn.querySelector('i');
    
    document.querySelectorAll('audio, video').forEach(m => { if(m!==audio && m!==video) m.pause(); });
    
    if(audio){
        if(audio.paused){
            audio.play();
            if(video){ video.muted=true; video.play(); }
            icon.className='bi bi-pause-fill';
        } else {
            audio.pause(); if(video) video.pause();
            icon.className='bi bi-play-fill';
        }
    }
}
</script>
</body>
</html>