<?php
include "../config/db.php";

// 1. HANDLE ADD / MODIFY REVIEWS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = mysqli_real_escape_string($conn, $_POST['music_id']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $review_id = isset($_POST['review_id']) ? $_POST['review_id'] : null;

    if ($review_id) {
        // MODIFY EXISTING
        $sql = "UPDATE reviews SET rating='$rating', comment='$comment' WHERE id='$review_id'";
    } else {
        // ADD NEW
        $sql = "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')";
    }
    
    if(mysqli_query($conn, $sql)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success");
        exit();
    }
}

// 2. FETCH MUSIC WITH CALCULATED RATINGS
$query = "SELECT m.*, 
          ROUND(AVG(r.rating), 1) as avg_rating, 
          COUNT(r.id) as total_reviews 
          FROM music m 
          LEFT JOIN reviews r ON m.id = r.music_id 
          GROUP BY m.id 
          ORDER BY m.id DESC";
$music_result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Studio | Pro Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0a0a0a;
            --card-bg: #141414;
            --accent: #ff0055;
            --accent-grad: linear-gradient(45deg, #ff0055, #ff5e00);
            --text-muted: #888;
        }

        body { background: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 20px 0; }
        
        /* Header */
        .header-section { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 25px; border-bottom: 1px solid #222; padding-bottom: 15px;
        }
        .search-box { background: #1a1a1a; border: 1px solid #333; color: white; border-radius: 6px; padding: 8px 15px; width: 280px; font-size: 0.85rem; }

        /* Grid & Cards */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .music-card { 
            background: var(--card-bg); border-radius: 12px; padding: 15px; 
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #222; text-align: center;
        }
        .music-card:hover { transform: translateY(-5px); border-color: var(--accent); background: #1c1c1c; }

        /* Disc Animation */
        .disc-wrapper {
            position: relative; width: 90px; height: 90px; margin: 0 auto 15px;
            border-radius: 50%; background: #000; display: flex; align-items: center;
            justify-content: center; border: 4px solid #222; box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }
        .disc-wrapper i { font-size: 2.5rem; color: var(--accent); }
        .playing .disc-wrapper { animation: rotateDisc 2s linear infinite; border-color: var(--accent); }
        @keyframes rotateDisc { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .play-trigger {
            position: absolute; width: 35px; height: 35px; background: var(--accent-grad);
            border-radius: 50%; border: none; color: white; opacity: 0; transition: 0.3s;
        }
        .music-card:hover .play-trigger { opacity: 1; }

        /* Info */
        .title { font-weight: 700; font-size: 0.9rem; margin: 0; color: #fff; }
        .artist { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 10px; }
        
        /* Ratings */
        .rating-box { font-size: 0.75rem; color: #ffca08; margin-bottom: 12px; }
        .rev-badge { background: #222; color: #aaa; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; cursor: pointer; text-decoration: none; }
        .rev-badge:hover { background: var(--accent); color: white; }

        /* Modal Customization */
        .modal-content { background: #111; border: 1px solid #333; }
        .form-control, .form-select { background: #1a1a1a; border: 1px solid #333; color: #fff; font-size: 0.9rem; }
        .form-control:focus { background: #222; color: #fff; border-color: var(--accent); box-shadow: none; }
        
        .recent-rev-list { max-height: 200px; overflow-y: auto; background: #0c0c0c; border-radius: 8px; padding: 10px; margin-top: 15px; }
        .rev-item { border-bottom: 1px solid #222; padding: 8px 0; }
        .rev-item:last-child { border: 0; }

        audio { width: 100%; height: 30px; margin-top: 15px; filter: invert(100%); opacity: 0.7; }
        footer { padding: 40px; text-align: center; font-size: 0.7rem; color: #444; letter-spacing: 2px; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">SOUND<span style="color: var(--accent);">STUDIO</span></h2>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search track or artist...">
            <a href="javascript:history.back()" class="btn btn-outline-light btn-sm px-3">
                <i class="bi bi-chevron-left"></i>
            </a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php while ($row = mysqli_fetch_assoc($music_result)): 
            $avg = $row['avg_rating'] ?? 0;
        ?>
            <div class="music-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                
                <div class="disc-wrapper">
                    <i class="bi bi-vinyl-fill"></i>
                    <button class="play-trigger" onclick="toggleMusic(this)">
                        <i class="bi bi-play-fill"></i>
                    </button>
                </div>

                <div class="info">
                    <p class="title"><?= htmlspecialchars($row['title']); ?></p>
                    <p class="artist"><?= htmlspecialchars($row['artist']); ?></p>
                    
                    <div class="rating-box">
                        <?php for($i=1; $i<=5; $i++) echo ($i <= floor($avg)) ? '★' : '☆'; ?>
                        <span class="text-white ms-1"><?= $avg ?></span>
                    </div>

                    <a href="#" class="rev-badge" data-bs-toggle="modal" data-bs-target="#modal<?= $row['id'] ?>">
                        <i class="bi bi-plus-circle-fill me-1"></i> <?= $row['total_reviews'] ?> Reviews
                    </a>
                </div>

                <audio class="audio-player" onplay="handlePlay(this)" onpause="handlePause(this)">
                    <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                </audio>

                <div class="modal fade" id="modal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-start">
                            <form method="POST">
                                <div class="modal-header border-0">
                                    <h6 class="modal-title fw-bold">Feedback: <?= htmlspecialchars($row['title']) ?></h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body pt-0">
                                    <input type="hidden" name="music_id" value="<?= $row['id'] ?>">
                                    
                                    <label class="small text-muted mb-1">Select Rating</label>
                                    <select name="rating" class="form-select mb-3" required>
                                        <option value="5">5 Stars - Amazing</option>
                                        <option value="4">4 Stars - Great</option>
                                        <option value="3">3 Stars - Good</option>
                                        <option value="2">2 Stars - Poor</option>
                                        <option value="1">1 Star - Bad</option>
                                    </select>
                                    
                                    <label class="small text-muted mb-1">Your Comment</label>
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Tell us what you think..." required></textarea>
                                    
                                    <button type="submit" name="submit_review" class="btn btn-sm w-100 mt-3" style="background: var(--accent-grad); color:white; font-weight:600;">SUBMIT FEEDBACK</button>

                                    <div class="recent-rev-list">
                                        <p class="x-small fw-bold text-uppercase" style="font-size:0.6rem; color:var(--accent)">Community Reviews</p>
                                        <?php 
                                        $m_id = $row['id'];
                                        $reviews = mysqli_query($conn, "SELECT * FROM reviews WHERE music_id = $m_id ORDER BY id DESC LIMIT 5");
                                        if(mysqli_num_rows($reviews) > 0):
                                            while($rev = mysqli_fetch_assoc($reviews)): ?>
                                                <div class="rev-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-warning" style="font-size:0.7rem;">
                                                            <?php for($k=1; $k<=5; $k++) echo ($k <= $rev['rating']) ? '★' : '☆'; ?>
                                                        </span>
                                                        <button type="button" onclick="editReview('<?= $rev['id'] ?>', '<?= $rev['rating'] ?>', `<?= addslashes($rev['comment']) ?>`, '<?= $row['id'] ?>')" class="btn p-0 text-info" style="font-size:0.6rem;">Edit</button>
                                                    </div>
                                                    <p class="small mb-0 mt-1 text-light-50"><?= htmlspecialchars($rev['comment']) ?></p>
                                                </div>
                                            <?php endwhile; 
                                        else: echo "<p class='small text-muted'>No reviews yet.</p>"; endif; ?>
                                    </div>
                                </div>
                                <input type="hidden" name="review_id" id="edit_id_<?= $row['id'] ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<footer>&copy; 2026 SOUND ENTERTAINMENT PRODUCTIONS</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search Filter
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".music-card").forEach(card => {
            let content = card.dataset.title + " " + card.dataset.artist;
            card.style.display = content.includes(val) ? "block" : "none";
        });
    });

    // Edit Review Helper
    function editReview(id, rating, comment, musicId) {
        const modal = document.querySelector(`#modal${musicId}`);
        modal.querySelector('select[name="rating"]').value = rating;
        modal.querySelector('textarea[name="comment"]').value = comment;
        modal.querySelector(`#edit_id_${musicId}`).value = id;
        modal.querySelector('button[name="submit_review"]').innerHTML = "UPDATE REVIEW";
    }

    // Audio Logic
    function toggleMusic(btn) {
        const card = btn.closest('.music-card');
        const audio = card.querySelector('audio');
        if (audio.paused) {
            document.querySelectorAll('audio').forEach(a => {
                a.pause();
                a.closest('.music-card').classList.remove('playing');
            });
            audio.play();
        } else {
            audio.pause();
        }
    }

    function handlePlay(el) { el.closest('.music-card').classList.add('playing'); el.closest('.music-card').querySelector('.play-trigger i').className = 'bi bi-pause-fill'; }
    function handlePause(el) { el.closest('.music-card').classList.remove('playing'); el.closest('.music-card').querySelector('.play-trigger i').className = 'bi bi-play-fill'; }
</script>
</body>
</html>