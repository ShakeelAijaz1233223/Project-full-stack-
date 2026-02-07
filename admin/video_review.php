<?php
include "../config/db.php";

// 1. Delete Review Logic
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    mysqli_query($conn, "DELETE FROM video_reviews WHERE id = $id");
    header("Location: video_reviews.php?msg=deleted");
    exit();
}

// 2. Fetch Video Reviews with Video Titles
$query = "SELECT video_reviews.*, videos.title as v_title 
          FROM video_reviews 
          JOIN videos ON video_reviews.video_id = videos.id 
          ORDER BY video_reviews.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Video Feedback Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --accent: #ff0055; --bg: #0a0a0a; --card: #111; }
        body { background: var(--bg); color: #fff; font-family: 'Inter', sans-serif; padding: 30px; }
        .glass-card { 
            background: var(--card); border: 1px solid #222; border-radius: 15px; padding: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .table { color: #eee; border-color: #222; margin-top: 20px; }
        .table thead { background: #1a1a1a; color: var(--accent); font-size: 0.8rem; text-transform: uppercase; }
        .stars { color: #ffca08; }
        .btn-action { padding: 5px 10px; border-radius: 6px; text-decoration: none; transition: 0.3s; }
        .btn-del { background: rgba(255, 0, 85, 0.1); color: #ff0055; }
        .btn-del:hover { background: #ff0055; color: #fff; }
        .video-title { font-weight: 600; color: #fff; }
        .comment-text { color: #888; font-style: italic; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="bi bi-star-fill text-warning me-2"></i>VIDEO <span style="color:var(--accent)">REVIEWS</span></h2>
            <p class="text-muted mb-0">Manage and moderate all visual content feedback</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm px-4 rounded-pill">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success bg-success text-white border-0 py-2 small">Review deleted successfully.</div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Video Project</th>
                        <th>Rating</th>
                        <th>Feedback</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="video-title"><?= htmlspecialchars($row['v_title']) ?></td>
                            <td>
                                <div class="stars">
                                    <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                                </div>
                            </td>
                            <td class="comment-text">"<?= htmlspecialchars($row['comment']) ?>"</td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="?del=<?= $row['id'] ?>" class="btn-action btn-del" onclick="return confirm('Delete this review permanently?')">
                                    <i class="bi bi-trash3-fill"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No video reviews found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>