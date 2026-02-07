<?php
include "../config/db.php";

// Delete Logic
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    mysqli_query($conn, "DELETE FROM video_reviews WHERE id = $id");
    header("Location: album_reviews.php");
}

// Fetch Reviews specifically for Albums (using JOIN)
$query = "SELECT video_reviews.*, albums.title as album_name 
          FROM video_reviews 
          JOIN albums ON video_reviews.video_id = albums.id 
          ORDER BY video_reviews.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Album Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #050505; color: #fff; padding: 40px; }
        .table-card { background: #111; border: 1px solid #222; border-radius: 15px; padding: 20px; }
        .stars { color: #ffca08; }
        .btn-del { color: #ff0055; transition: 0.3s; }
        .btn-del:hover { transform: scale(1.2); color: #ff5e00; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between mb-4">
        <h3><i class="bi bi-journal-album me-2"></i>Album Feedback</h3>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
    </div>

    <div class="table-card">
        <table class="table table-dark table-hover">
            <thead>
                <tr class="text-muted small">
                    <th>ALBUM</th>
                    <th>RATING</th>
                    <th>COMMENT</th>
                    <th>DATE</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr class="align-middle">
                    <td><b class="text-info"><?= $row['album_name'] ?></b></td>
                    <td class="stars">
                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                    </td>
                    <td><small><?= htmlspecialchars($row['comment']) ?></small></td>
                    <td class="text-muted small"><?= date('d M', strtotime($row['created_at'])) ?></td>
                    <td>
                        <a href="?del=<?= $row['id'] ?>" class="btn-del" onclick="return confirm('Delete this review?')">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>