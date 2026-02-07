<?php
include "../config/db.php";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $music_id = $_POST['music_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO reviews (music_id, rating, comment) VALUES ('$music_id', '$rating', '$comment')");
    header("Location: " . $_SERVER['PHP_SELF'] . "?status=reviewed");
    exit();
}

// Fetch Music with Average Ratings
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
<title>Music Studio | Pro Home</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
/* ===== SAME CSS – NO CHANGE ===== */
:root {
    --bg: #0d0d0d;
    --card: #1b1b1b;
    --accent: #ff3366;
    --accent-grad: linear-gradient(135deg, #ff3366, #ff9933);
    --text-main: #f5f5f5;
    --text-muted: #999;
    --shadow: rgba(0,0,0,0.8);
}

body {
    background: var(--bg);
    color: var(--text-main);
    font-family: 'Inter', sans-serif;
    margin: 0;
    overflow-x: hidden;
}

.studio-wrapper {
    width: 95%;
    margin: 0 auto;
    padding: 25px 0;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #222;
    padding-bottom: 15px;
    margin-bottom: 30px;
}

.search-box {
    background: #1f1f1f;
    border: 1px solid #333;
    color: var(--text-main);
    border-radius: 10px;
    padding: 8px 16px;
    width: 280px;
}

.btn-back {
    background: #222;
    color: #fff;
    padding: 7px 18px;
    border-radius: 10px;
    text-decoration: none;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.music-card {
    background: var(--card);
    border-radius: 20px;
    padding: 12px;
    border: 1px solid #2a2a2a;
}

.media-wrapper {
    position: relative;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 15px;
    margin-bottom: 15px;
}

.record-icon {
    font-size: 4rem;
    color: #222;
}

.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--accent-grad);
    border-radius: 50%;
    border: none;
    color: #fff;
}

.title {
    font-size: 1rem;
    font-weight: 700;
}

.meta-info {
    display: flex;
    gap: 8px;
    margin: 5px 0 10px;
    font-size: 0.75rem;
}

.meta-info span {
    background: rgba(255,255,255,0.08);
    padding: 2px 8px;
    border-radius: 5px;
}

.artist-tag {
    color: var(--accent);
}

footer {
    text-align: center;
    padding: 40px;
    color: #444;
}
</style>
</head>

<body>

<div class="studio-wrapper">

<div class="header-section">
<h4>Music<span style="color:var(--accent)">Studio</span></h4>
<div>
<input type="text" id="search" class="search-box" placeholder="Search title or artist...">
<a href="index.php" class="btn-back">Home</a>
</div>
</div>

<div class="grid">
<?php while ($row = mysqli_fetch_assoc($music)):
$avg = round($row['avg_rating'], 1);
?>
<div class="music-card">

<div class="media-wrapper">
<i class="bi bi-disc record-icon"></i>
<button class="play-btn">▶</button>
</div>

<p class="title"><?= htmlspecialchars($row['title']) ?></p>

<div class="meta-info">
<span class="artist-tag"><?= htmlspecialchars($row['artist']) ?></span>
<span><?= htmlspecialchars($row['album'] ?? '') ?></span>
<span><?= htmlspecialchars($row['year'] ?? '') ?></span>
</div>

</div>
<?php endwhile; ?>
</div>

</div>

<footer>© 2026 Music Studio Pro</footer>

</body>
</html>
