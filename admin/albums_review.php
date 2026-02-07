<?php
session_start();
include "../config/db.php";

// 1. Handle Delete Review
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM album_reviews WHERE id = $id");
    header("Location: album_reviews_manage.php?status=deleted");
    exit();
}

// 2. Fetch all reviews with Album Titles
$query = "SELECT album_reviews.*, albums.title as album_name, albums.cover 
          FROM album_reviews 
          JOIN albums ON album_reviews.album_id = albums.id 
          ORDER BY album_reviews.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Album Reviews | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --bg-dark: #080808; --card-bg: #121212; --accent: #ff0055; }
        body { background-color: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; }
        .table-container { background: var(--card-bg); border-radius: 15px; padding: 20px; border: 1px solid #222; margin-top: 30px; }
        .table { color: #fff; border-color: #222; }
        .table thead { background: #1a1a1a; }
        .album-img { width: 40px; height: 40px; border-radius: 5px; object-fit: cover; margin-right: 10px; }
        .stars { color: #ffca08; }
        .btn-action { padding: 4px 8px; font-size: 0.8rem; }
        .status-msg { font-size: 0.85rem; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .badge-rating { background: var(--accent); color: white; font-size: 0.75rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">ALBUM <span style="color: var(--accent);">REVIEWS</span></h3>
        <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <div class="alert alert-danger status-msg bg-danger text-white border-0">Review deleted successfully!</div>
    <?php endif; ?>

    <div class="table-container shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Album</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/albums/<?= $row['cover'] ?>" class="album-img">
                                        <span class="fw-semibold"><?= htmlspecialchars($row['album_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="stars">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                                    </div>
                                    <small class="text-muted"><?= $row['rating'] ?>/5</small>
                                </td>
                                <td style="max-width: 300px;">
                                    <div class="text-truncate" title="<?= htmlspecialchars($row['comment']) ?>">
                                        <?= htmlspecialchars($row['comment']) ?>
                                    </div>
                                </td>
                                <td><small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small></td>
                                <td class="text-center">
                                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-outline-danger btn-action" 
                                       onclick="return confirm('Are you sure you want to delete this review?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No reviews found yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>