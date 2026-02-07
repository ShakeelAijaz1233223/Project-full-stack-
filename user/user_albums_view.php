<?php
session_start();
include "../config/db.php";

// 1. Handle Review Submission (Sabse upar rakhein taaki header redirect kaam kare)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $album_id = mysqli_real_escape_string($conn, $_POST['album_id']);
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Check if table exists (Using 'reviews' as per your initial schema or 'album_reviews')
    $review_query = "INSERT INTO reviews (music_id, rating, comment) VALUES ('$album_id', '$rating', '$comment')";
    
    if(mysqli_query($conn, $review_query)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
        exit();
    }
}

// 2. Build Filter Logic
$filter = "";
$where_clauses = [];

if (!empty($_GET['genre'])) {
    $genre = mysqli_real_escape_string($conn, $_GET['genre']);
    $where_clauses[] = "genre = '$genre'";
}
if (!empty($_GET['artist'])) {
    $artist = mysqli_real_escape_string($conn, $_GET['artist']);
    $where_clauses[] = "artist = '$artist'";
}
if (!empty($_GET['year'])) {
    $year = mysqli_real_escape_string($conn, $_GET['year']);
    $where_clauses[] = "year = '$year'";
}

if (count($where_clauses) > 0) {
    $filter = " WHERE " . implode(" AND ", $where_clauses);
}

// 3. Main Query (Music Studio se sara data filter ke saath)
$query = "SELECT *, 
          (SELECT AVG(rating) FROM reviews WHERE reviews.music_id = music_studio.id) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE reviews.music_id = music_studio.id) as total_reviews
          FROM music_studio $filter ORDER BY id DESC";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg: #0d0d0d; --card: #1b1b1b; --accent: #ff3366;
            --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
            --text-main: #f5f5f5; --text-muted: #999;
        }

        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 25px 0; }

        /* Header */
        .header-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 30px; }
        .search-box { background: #1f1f1f; border: 1px solid #333; color: var(--text-main); border-radius: 10px; padding: 8px 16px; width: 280px; }

        /* Grid & Cards */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .album-card { background: var(--card); border-radius: 20px; padding: 12px; border: 1px solid #2a2a2a; transition: 0.3s; }
        .album-card:hover { transform: translateY(-8px); border-color: var(--accent); }

        /* Media */
        .media-wrapper { position: relative; width: 100%; aspect-ratio: 1/1; background: #000; border-radius: 15px; overflow: hidden; margin-bottom: 15px; }
        .media-wrapper img, .media-wrapper video { width: 100%; height: 100%; object-fit: cover; }
        
        .play-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 50px; height: 50px; background: var(--accent-grad); border-radius: 50%; border: none; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; z-index: 10; opacity: 0; transition: 0.3s; }
        .album-card:hover .play-btn { opacity: 1; }

        .title { font-size: 1rem; font-weight: 700; margin-bottom: 5px; }
        .meta-info span { background: rgba(255,255,255,0.08); padding: 2px 8px; border-radius: 5px; font-size: 0.75rem; color: var(--text-muted); }
        .artist-tag { color: var(--accent) !important; font-weight: 600; }
        
        .stars-display { color: #ffd700; font-size: 0.8rem; margin: 10px 0; }
        .rev-btn { width: 100%; padding: 8px; border-radius: 10px; border: none; background: #222; color: #fff; font-size: 0.8rem; font-weight: 600; }
        .rev-btn:hover { background: var(--accent); }

        /* Review Overlay */
        #reviewOverlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; }
        .review-box { background: #151515; padding: 30px; border-radius: 20px; width: 90%; max-width: 400px; border: 1px solid #333; }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; margin-bottom: 20px; }
        .star-rating label { font-size: 2.5rem; color: #333; cursor: pointer; }
        .star-rating input:checked ~ label { color: #ffd700; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h4 class="m-0 fw-bold">Music<span style="color: var(--accent);">Studio</span></h4>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search title or artist...">
            <a href="albums.php" class="btn-back"><i class="bi bi-collection"></i> Albums</a>
        </div>
    </div>

    <div class="grid" id="albumGrid">
        
            <div class="album-card" data-search="<?= strtolower($row['title'] . ' ' . $row['artist']); ?>">
                <div class="media-wrapper">
                    <?php if (!empty($row['video'])): ?>
                        <video id="vid-<?= $row['id'] ?>" loop playsinline poster="../admin/uploads/covers/<?= $row['cover'] ?>">
                            <source src="../admin/uploads/videos/<?= $row['video'] ?>" type="video/mp4">
                        </video>
                        <button class="play-btn" onclick="handleMedia('<?= $row['id'] ?>', this)">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    <?php else: ?>
                        <img src="../admin/uploads/covers/<?= $row['cover'] ?>" alt="Cover">
                    <?php endif; ?>
                </div>

                <p class="title text-truncate"><?= htmlspecialchars($row['title']) ?></p>
                
                <div class="meta-info">
                    <span class="artist-tag"><?= htmlspecialchars($row['artist']) ?></span>
                    <span><?= htmlspecialchars($row['genre']) ?></span>
                    <span><?= htmlspecialchars($row['year']) ?></span>
                </div>

                <div class="stars-display">
                    <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span style="color: #666; font-size: 0.7rem;">(<?= $row['total_reviews'] ?>)</span>
                </div>

                <button class="rev-btn" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                    <i class="bi bi-chat-square-text me-2"></i>ADD REVIEW
                </button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-box">
        <h5 class="text-center mb-1" id="revTitle">Track Name</h5>
        <form method="POST">
            <input type="hidden" name="album_id" id="revAlbumId">
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="3" placeholder="Write a comment..." required></textarea>
            <div class="row g-2">
                <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeReview()">CANCEL</button></div>
                <div class="col-6"><button type="submit" name="submit_review" class="btn btn-primary w-100" style="background: var(--accent); border:none;">SUBMIT</button></div>
            </div>
        </form>
    </div>
</div>

<script>
    // Search Filter
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".album-card").forEach(card => {
            card.style.display = card.getAttribute('data-search').includes(val) ? "block" : "none";
        });
    });

    // Play/Pause Video
    function handleMedia(id, btn) {
        const video = document.getElementById('vid-' + id);
        const icon = btn.querySelector('i');
        if (video.paused) { video.play(); icon.className = 'bi bi-pause-fill'; } 
        else { video.pause(); icon.className = 'bi bi-play-fill'; }
    }

    function openReview(id, title) {
        document.getElementById('revAlbumId').value = id;
        document.getElementById('revTitle').innerText = title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }

    function closeReview() { document.getElementById('reviewOverlay').style.display = 'none'; }
</script>
</body>
</html>