<?php
session_start();
include "../config/db.php";

// Handle Review Submission inside same page or separate (we'll use separate for cleanliness)
// Handle Delete Album
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

// Fetch albums
$albums = mysqli_query($conn, "SELECT * FROM albums ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Albums Studio | Compact Pro</title>
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
.search-box { background: #1a1a1a; border: 1px solid #222; color: white; border-radius: 4px; padding: 6px 15px; width: 250px; font-size: 0.85rem; }
.btn-back, .btn-delete, .btn-edit { background: #1a1a1a; border: 1px solid #222; color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: 0.3s ease; }
.btn-back:hover, .btn-delete:hover, .btn-edit:hover { background: #222; border-color: var(--accent); color: #fff; }

/* Grid adjusted for more height for reviews */
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
.card { background: var(--card-bg); border: 1px solid #1a1a1a; border-radius: 10px; overflow: hidden; transition: 0.3s ease; padding: 10px; position: relative; }
.card:hover { transform: translateY(-5px); border-color: #333; }

.media-wrapper { position: relative; width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
video { width: 100%; height: 100%; object-fit: cover; }
.play-overlay { position: absolute; width: 40px; height: 40px; background: var(--accent-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; transition: 0.3s; z-index: 10; border: none; color: white; }
.card:hover .play-overlay { opacity: 1; }

.title { font-weight: 600; font-size: 0.9rem; color: #fff; margin-top: 5px; }
.artist { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px; }

/* Review Styles */
.rating-stars { color: #ffcc00; font-size: 0.8rem; margin-bottom: 5px; }
.review-form input, .review-form textarea, .review-form select { 
    background: #000 !important; border: 1px solid #222 !important; color: #fff !important; font-size: 0.7rem !important; 
}
.review-form button { font-size: 0.65rem; padding: 2px; background: var(--accent); border: none; width: 100%; border-radius: 3px; color: white; margin-top: 4px; }
.recent-reviews { font-size: 0.65rem; color: #666; text-align: left; max-height: 40px; overflow-y: auto; border-top: 1px solid #1a1a1a; padding-top: 5px; margin-top: 5px; }

.card-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 6px; z-index: 20; }
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
        <div class="alert alert-success py-2" style="font-size: 0.8rem;"><?= $msg ?></div>
    <?php endif; ?>

    <div class="grid" id="albumGrid">
        <?php if (mysqli_num_rows($albums) > 0):
            while ($row = mysqli_fetch_assoc($albums)):
                $id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $artist = htmlspecialchars($row['artist']);
                $video = $row['video'];
                $cover = $row['cover'];

                // Calculate Rating
                $rat_q = mysqli_query($conn, "SELECT AVG(rating) as avg_r, COUNT(id) as total FROM album_reviews WHERE album_id=$id");
                $rat_data = mysqli_fetch_assoc($rat_q);
                $avg = round($rat_data['avg_r'], 1);
        ?>
        <div class="card album-card" data-title="<?= strtolower($title); ?>" data-artist="<?= strtolower($artist); ?>">
            <div class="card-actions">
                <a href="?delete=<?= $id; ?>" class="btn-delete" onclick="return confirm('Delete?');"><i class="bi bi-trash"></i></a>
                <a href="edit_album.php?id=<?= $id; ?>" class="btn-edit"><i class="bi bi-pencil"></i></a>
            </div>

            <div class="media-wrapper">
                <?php if(!empty($video)): ?>
                    <video id="vid-<?= $id; ?>" preload="metadata" playsinline muted loop poster="../admin/uploads/albums/<?= $cover; ?>">
                        <source src="../admin/uploads/albums/<?= $video; ?>" type="video/mp4">
                    </video>
                    <button class="play-overlay" onclick="togglePlay('<?= $id; ?>', this)"><i class="bi bi-play-fill"></i></button>
                <?php else: ?>
                    <img src="../admin/uploads/albums/<?= $cover; ?>" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                <?php endif; ?>
            </div>

            <div class="card-body p-0">
                <div class="title text-truncate"><?= $title; ?></div>
                <div class="artist text-truncate"><?= $artist; ?></div>
                
                <div class="rating-stars">
                    <?= ($rat_data['total'] > 0) ? "⭐ $avg (".$rat_data['total'].")" : "No ratings"; ?>
                </div>

                <form action="submit_review.php" method="POST" class="review-form">
                    <input type="hidden" name="album_id" value="<?= $id; ?>">
                    <div class="d-flex gap-1 mb-1">
                        <select name="rating" class="form-select form-select-sm shadow-none" required>
                            <option value="5">5★</option><option value="4">4★</option>
                            <option value="3">3★</option><option value="2">2★</option><option value="1">1★</option>
                        </select>
                        <input type="text" name="user_name" class="form-control form-control-sm shadow-none" placeholder="Name" required>
                    </div>
                    <textarea name="review_text" class="form-control form-control-sm shadow-none" placeholder="Write review..." rows="1"></textarea>
                    <button type="submit" name="add_review">POST REVIEW</button>
                </form>

                <div class="recent-reviews">
                    <?php
                    $rev_list = mysqli_query($conn, "SELECT * FROM album_reviews WHERE album_id=$id ORDER BY id DESC LIMIT 2");
                    while($rev = mysqli_fetch_assoc($rev_list)){
                        echo "<div><strong>".htmlspecialchars($rev['user_name']).":</strong> ".htmlspecialchars($rev['review_text'])."</div>";
                    }
                    ?>
                </div>

                <?php if(!empty($row['audio'])): ?>
                    <audio id="aud-<?= $id; ?>" onplay="handleMediaPlay(this)">
                        <source src="../admin/uploads/albums/<?= $row['audio']; ?>" type="audio/mpeg">
                    </audio>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="text-center w-100 py-5"><p class="text-muted">Empty Studio</p></div>
        <?php endif; ?>
    </div>
</div>

<footer>&copy; 2026 ALBUMS STUDIO &bull; SOUND SYSTEM</footer>

<script>
// Real-time Search
document.getElementById("search").addEventListener("input", function(){
    let val = this.value.toLowerCase().trim();
    document.querySelectorAll(".album-card").forEach(card=>{
        let title = card.dataset.title;
        let artist = card.dataset.artist;
        card.style.display = (title.includes(val) || artist.includes(val)) ? "block" : "none";
    });
});

// Play/Pause Master
function togglePlay(id, btn){
    const audio = document.getElementById('aud-'+id);
    const video = document.getElementById('vid-'+id);
    const icon = btn.querySelector('i');
    document.querySelectorAll('audio, video').forEach(m=>{ if(m!==audio && m!==video) m.pause(); });
    document.querySelectorAll('.play-overlay i').forEach(i=>i.className='bi bi-play-fill');
    if(audio){
        if(audio.paused){ audio.play(); if(video){ video.muted=true; video.play(); } icon.className='bi bi-pause-fill'; }
        else{ audio.pause(); if(video) video.pause(); icon.className='bi bi-play-fill'; }
    }else if(video){
        if(video.paused){ video.muted=false; video.play(); icon.className='bi bi-pause-fill'; }
        else{ video.pause(); icon.className='bi bi-play-fill'; }
    }
}
function handleMediaPlay(current){ document.querySelectorAll('audio').forEach(a=>{ if(a!==current) a.pause(); }); }
</script>
</body>
</html>