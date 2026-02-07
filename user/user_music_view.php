<?php
include "../config/db.php";

// Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = $_POST['music_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query = "SELECT music.*, 
          (SELECT AVG(rating) FROM reviews WHERE reviews.music_id = music.id) as avg_rating,
          (SELECT COUNT(*) FROM reviews WHERE reviews.music_id = music.id) as total_reviews
          FROM music ORDER BY id DESC";
$music = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Studio | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --bg: #080808; --card: #111; --accent: #ff0055; }
        body { background: var(--bg); color: #fff; font-family: 'Inter', sans-serif; }
        .studio-wrapper { width: 95%; margin: 0 auto; padding: 20px 0; }
        .header-section { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 20px; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
        .music-card { background: var(--card); border-radius: 12px; padding: 15px; text-align: center; border: 1px solid #1a1a1a; transition: 0.3s; }
        .music-card:hover { border-color: var(--accent); transform: translateY(-5px); }
        
        .disc-wrapper { position: relative; width: 70px; height: 70px; margin: 0 auto 10px; border-radius: 50%; background: #000; display: flex; align-items: center; justify-content: center; border: 3px solid #222; }
        .disc-wrapper i { font-size: 1.8rem; color: var(--accent); }
        .playing .disc-wrapper { animation: rotate 3s linear infinite; border-color: var(--accent); }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .play-trigger { position: absolute; width: 30px; height: 30px; background: var(--accent); border-radius: 50%; border: none; color: #fff; opacity: 0; }
        .music-card:hover .play-trigger { opacity: 1; }

        .title { font-weight: 600; font-size: 0.8rem; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .artist { font-size: 0.7rem; color: #777; }
        .stars-display { color: #ffca08; font-size: 0.65rem; margin-top: 5px; }
        
        .btn-rev { background: #222; color: #fff; border: none; font-size: 0.65rem; padding: 4px 10px; border-radius: 4px; margin-top: 8px; width: 100%; }
        .btn-rev:hover { background: var(--accent); }

        /* Full Screen Review Overlay */
        #reviewOverlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center;
        }
        .review-box { background: #151515; width: 90%; max-width: 400px; padding: 30px; border-radius: 15px; border: 1px solid var(--accent); box-shadow: 0 0 30px rgba(255, 0, 85, 0.2); }
        
        /* Star Rating Selection */
        .rating-input { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .rating-input input { display: none; }
        .rating-input label { font-size: 2rem; color: #333; cursor: pointer; transition: 0.3s; }
        .rating-input label:hover, .rating-input label:hover ~ label, .rating-input input:checked ~ label { color: #ffca08; }

        .search-box { background: #151515; border: 1px solid #333; color: white; border-radius: 5px; padding: 5px 15px; }
        audio { width: 100%; height: 25px; margin-top: 10px; }
    </style>
</head>
<body>

<div class="studio-wrapper">
    <div class="header-section">
        <h2 class="m-0 fw-bold">SOUND<span style="color: var(--accent);">STUDIO</span></h2>
        <div class="d-flex gap-2">
            <input type="text" id="search" class="search-box" placeholder="Search...">
            <a href="javascript:history.back()" class="btn btn-dark btn-sm"><i class="bi bi-arrow-left"></i></a>
        </div>
    </div>

    <div class="grid" id="musicGrid">
        <?php while ($row = mysqli_fetch_assoc($music)): 
            $avg = round($row['avg_rating'], 1);
        ?>
            <div class="music-card" data-title="<?= strtolower($row['title']); ?>" data-artist="<?= strtolower($row['artist']); ?>">
                <div class="disc-wrapper">
                    <i class="bi bi-disc-fill"></i>
                    <button class="play-trigger" onclick="toggleMusic(this)"><i class="bi bi-play-fill"></i></button>
                </div>
                <p class="title"><?= htmlspecialchars($row['title']); ?></p>
                <p class="artist"><?= htmlspecialchars($row['artist']); ?></p>
                
                <div class="stars-display">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= $avg) ? '★' : '☆'; ?>
                    <span>(<?= $row['total_reviews'] ?>)</span>
                </div>

                <button class="btn-rev" onclick="openReview('<?= $row['id'] ?>', '<?= addslashes($row['title']) ?>')">WRITE REVIEW</button>

                <audio class="audio-player" onplay="handlePlay(this)" onpause="handlePause(this)">
                    <source src="../admin/uploads/music/<?= $row['file']; ?>" type="audio/mpeg">
                </audio>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="reviewOverlay">
    <div class="review-box">
        <h5 id="revTitle" class="text-center mb-3">Rate Music</h5>
        <form method="POST">
            <input type="hidden" name="music_id" id="revMusicId">
            
            <div class="rating-input">
                <input type="radio" name="rating" value="5" id="s5" required><label for="s5">★</label>
                <input type="radio" name="rating" value="4" id="s4"><label for="s4">★</label>
                <input type="radio" name="rating" value="3" id="s3"><label for="s3">★</label>
                <input type="radio" name="rating" value="2" id="s2"><label for="s2">★</label>
                <input type="radio" name="rating" value="1" id="s1"><label for="s1">★</label>
            </div>

            <textarea name="comment" class="form-control bg-dark text-white border-secondary mb-3" rows="4" placeholder="Your experience..." required></textarea>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary w-100" onclick="closeReview()">CANCEL</button>
                <button type="submit" name="submit_review" class="btn w-100" style="background: var(--accent); color: white;">SUBMIT</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReview(id, title) {
        document.getElementById('revMusicId').value = id;
        document.getElementById('revTitle').innerText = "Rate: " + title;
        document.getElementById('reviewOverlay').style.display = 'flex';
    }
    function closeReview() { document.getElementById('reviewOverlay').style.display = 'none'; }

    // Search & Audio Controls
    document.getElementById("search").addEventListener("input", function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll(".music-card").forEach(card => {
            let txt = card.dataset.title + " " + card.dataset.artist;
            card.style.display = txt.includes(val) ? "block" : "none";
        });
    });

    function toggleMusic(btn) {
        let card = btn.closest('.music-card');
        let audio = card.querySelector('audio');
        if (audio.paused) {
            document.querySelectorAll('audio').forEach(a => { a.pause(); a.closest('.music-card').classList.remove('playing'); });
            audio.play();
        } else { audio.pause(); }
    }
    function handlePlay(el) { el.closest('.music-card').classList.add('playing'); el.closest('.music-card').querySelector('.play-trigger i').className = 'bi bi-pause-fill'; }
    function handlePause(el) { el.closest('.music-card').classList.remove('playing'); el.closest('.music-card').querySelector('.play-trigger i').className = 'bi bi-play-fill'; }
</script>
</body>
</html>