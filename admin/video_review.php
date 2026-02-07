<?php
include "../config/db.php";

// Delete Review
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    mysqli_query($conn, "DELETE FROM video_reviews WHERE id = $id");
    header("Location: video_reviews.php");
}

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
    <title>Admin | Video Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #0a0a0a; color: #fff; padding: 40px; }
        .table-container { background: #111; border-radius: 15px; padding: 20px; border: 1px solid #222; }
        .stars { color: #ffca08; }
        .btn-del { color: #ff4444; text-decoration: none; font-size: 1.2rem; }
        .btn-del:hover { color: #ff0000; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-film me-2"></i> Video Feedback Management</h3>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
    </div>

    <div class="table-container">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>Video Title</th>
                    <th>Rating</th>
                    <th>User Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['v_title']) ?></strong></td>
                    <td class="stars">
                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                    </td>
                    <td><small class="text-white-50"><?= htmlspecialchars($row['comment']) ?></small></td>
                    <td>
                        <a href="?del=<?= $row['id'] ?>" class="btn-del" onclick="return confirm('Delete this review?')">
                            <i class="bi bi-trash3-fill"></i>
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