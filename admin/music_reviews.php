<?php
include "../config/db.php";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");
    header("Location: reviews.php");
}

$query = "SELECT reviews.*, music.title as music_title 
          FROM reviews 
          JOIN music ON reviews.music_id = music.id 
          ORDER BY reviews.id DESC";
$res = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Manage Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f0f0f; color: #eee; padding: 30px; }
        .table { background: #1a1a1a; color: #fff; border-radius: 10px; overflow: hidden; }
        .rating-star { color: #ffca08; }
        .btn-delete { color: #ff4444; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="bi bi-star-fill"></i> User Reviews Management</h4>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
        </div>

        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>Track</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['music_title']) ?></strong></td>
                    <td>
                        <span class="rating-star">
                            <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                        </span>
                    </td>
                    <td><small><?= htmlspecialchars($row['comment']) ?></small></td>
                    <td><small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this review?')" class="btn-delete">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>