<?php
session_start();
include "../config/db.php";

// 1. Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $album_id = mysqli_real_escape_string($conn, $_POST['album_id']);
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    mysqli_query($conn, "INSERT INTO album_reviews (album_id, rating, comment) VALUES ('$album_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
    exit();
}

// 2. Fetch Data (Optimized Query)
$query = "SELECT albums.*, 
          (SELECT AVG(rating) FROM album_reviews WHERE album_id = albums.id) as avg_rating,
          (SELECT COUNT(*) FROM album_reviews WHERE album_id = albums.id) as total_reviews
          FROM albums ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro Studio | Multi-Search Explorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg: #080808;
            --card: #121212;
            --accent: #ff3366;
            --text-main: #ffffff;
            --text-muted: #b3b3b3;
        }

        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 30px 0; }

        /* Search Bar UI */
        .search-container { position: relative; max-width: 500px; margin: 0 auto 40px; }
        .search-box { 
            width: 100%; background: #1a1a1a; border: 1px solid #333; 
            padding: 12px 20px 12px 45px; border-radius: 30px; color: white;
            transition: 0.3s;
        }
        .search-box:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 15px rgba(255, 51, 102, 0.2); }
        .search-icon { position: absolute; left: 18px; top: 13px; color: var(--text-muted); }

        /* Album Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; }
        .album-card { 
            background: var(--card); border-radius: 18px; padding: 15px; 
            border: 1px solid #222; transition: 0.3s ease-in-out;
        }
        .album-card:hover { transform: translateY(-8px); border-color: var(--accent); }

        .media-box { 
            position: relative; width: 100%; aspect-ratio: 1/1; 
            border-radius: 12px; overflow: hidden; background: #000;
        }
        .media-box img, .media-box video { width: 100%; height: 100%; object-fit: cover; }
        
        .play-overlay {
            position: absolute; inset: 0; background: rgba(0,0,0,0.4);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: 0.3s; cursor: pointer;
        }
        .album-card:hover .play-overlay { opacity: 1; }
        .play-icon { font-size: 3rem; color: white; }

        .title { font-size: 1.1rem; font-weight: 700; margin-top: 12px; margin-bottom: 2px; }
        .meta-text { color: var(--text-muted); font-size: 0.85rem; line-height: 1.4; }
        .badge-year { background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; }

        /* Review Modal */
        #revOverlay { 
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); 
            z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px);
        }
        .rev-modal { background: #181818; padding: 30px; border-radius: 20px; width: 400px; border: 1px solid #333; }
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; font-size: 2rem; }
        .star-rating input { display: none; }
        .star-rating label { color: #333; cursor: pointer; }
        .star-rating label:hover, .star-rating label:hover~label, .star-rating input:checked~label { color: #ffd700; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="text-center mb-4">
        <h2 class="fw-bold">STUDIO<span style="color:var(--accent)">EXPLORER</span></h2>
        <p class="text-muted">Search by Name, Artist, Year, or Album</p>
    </div>

    <div class="search-container">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="searchInput" class="search-box" placeholder="Try '2023', 'Arijit Singh', or 'Soulful'...">
    </div>

    <div class="grid" id="albumGrid">
        <?php while($row = mysqli_fetch_assoc($result)): 
            $avg = round($row['avg_rating'], 1);
            // Searchable string combining all fields
            $searchString = strtolower($row['title'] . ' ' . $row['artist'] . ' ' . $row['album'] . ' ' . $row['year']);
        ?>
            <div class="album-card" data-info="<?= $searchString ?>">
                <div class="media-box">
                    <?php if(!empty($row['video'])): ?>
                        <video id="v-<?= $row['id'] ?>" poster="../admin/uploads/albums/<?= $row['cover'] ?>">
                            <source src="../admin/uploads/albums/<?= $row['video'] ?>" type="video/mp4">
                        </video>
                        <div class="play-overlay" onclick="toggleVid('v-<?= $row['id'] ?>')">
                            <i class="bi bi-play-circle-fill play-icon"></i>
                        </div>
                    <?php else: ?>
                        <img src="../admin/uploads/albums/<?= $row['cover'] ?>">
                    <?php endif; ?>
                </div>

                <div class="title text-truncate"><?= htmlspecialchars($row['title']) ?></div>
                <div class="meta-text">
                    <div><i class="bi bi-person me-1"></i><?= htmlspecialchars($row['artist']) ?></div>
                    <div class="d-flex justify-content-between mt-1">
                        <span><i class="bi bi-disc me-1"></i><?= htmlspecialchars($row['album']) ?></span>
                        <span class="badge-year"><?= $row['year'] ?></span>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-warning small">
                        <?php for($i=1; $i<=5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    </div>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="openRev('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">
                        Rate <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="revOverlay">
    <div class="rev-modal">
        <h5 id="rtitle" class="text-center">Rate Item</h5>
        <form method="POST">
            <input type="hidden" name="album_id" id="rid">
            <div class="star-rating my-3">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>
            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" placeholder="Write something..." required></textarea>
            <div class="row g-2">
                <div class="col-6"><button type="button" class="btn btn-secondary w-100" onclick="closeRev()">Cancel</button></div>
                <div class="col-6"><button type="submit" name="submit_review" class="btn btn-danger w-100" style="background:var(--accent)">Post</button></div>
            </div>
        </form>
    </div>
</div>

<script>
    // Real-Time Multi-Field Search Logic
    document.getElementById('searchInput').addEventListener('input', function() {
        let filter = this.value.toLowerCase().trim();
        let cards = document.querySelectorAll('.album-card');
        
        cards.forEach(card => {
            let info = card.getAttribute('data-info');
            card.style.display = info.includes(filter) ? "block" : "none";
        });
    });

    // Video Control
    function toggleVid(id) {
        let v = document.getElementById(id);
        document.querySelectorAll('video').forEach(vid => { if(vid !== v) vid.pause(); });
        v.paused ? v.play() : v.pause();
    }

    // Modal Control
    function openRev(id, title) {
        document.getElementById('rid').value = id;
        document.getElementById('rtitle').innerText = title;
        document.getElementById('revOverlay').style.display = 'flex';
    }
    function closeRev() { document.getElementById('revOverlay').style.display = 'none'; }
</script>

</body>
</html>