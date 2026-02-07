<?php
session_start();
include "../config/db.php";

// 1. DELETE LOGIC (Top par rakhein taaki output se pehle execute ho)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Pehle check karein review exist karta hai ya nahi
    $check = mysqli_query($conn, "SELECT id FROM album_reviews WHERE id = $id");
    if(mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM album_reviews WHERE id = $id");
        // Redirecting back to the SAME file name
        header("Location: album_reviews_manage.php?status=deleted");
        exit();
    } else {
        echo "Review not found!";
        exit();
    }
}

// 2. FETCH REVIEWS
$query = "SELECT album_reviews.*, albums.title as album_name, albums.cover 
          FROM album_reviews 
          LEFT JOIN albums ON album_reviews.album_id = albums.id 
          ORDER BY album_reviews.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Album Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --bg-dark: #080808; --card-bg: #121212; --accent: #ff0055; }
        body { background-color: var(--bg-dark); color: #fff; font-family: 'Inter', sans-serif; }
        .table-container { background: var(--card-bg); border-radius: 12px; padding: 20px; border: 1px solid #222; margin-top: 20px; }
        .table { color: #fff; border-color: #222; vertical-align: middle; }
        .album-img { width: 50px; height: 50px; border-radius: 6px; object-fit: cover; border: 1px solid #333; }
        .stars { color: #ffca08; }
        .btn-delete { color: #ff4d4d; border: 1px solid #ff4d4d; padding: 5px 10px; border-radius: 6px; text-decoration: none; transition: 0.3s; }
        .btn-delete:hover { background: #ff4d4d; color: white; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-uppercase">Album <span style="color: var(--accent);">Reviews</span></h4>
        <a href="index.php" class="btn btn-outline-light btn-sm px-3">Dashboard</a>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-success bg-success text-white border-0 py-2 small">Review deleted successfully!</div>
    <?php endif; ?>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead class="text-muted small">
                    <tr>
                        <th>ALBUM</th>
                        <th>RATING</th>
                        <th>COMMENT</th>
                        <th>DATE</th>
                        <th class="text-center">REMOVE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/albums/<?= $row['cover'] ?>" class="album-img me-3">
                                        <span class="fw-bold"><?= htmlspecialchars($row['album_name'] ?? 'Deleted Album') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="stars small">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                                    </div>
                                    <span class="text-muted small"><?= $row['rating'] ?>/5</span>
                                </td>
                                <td class="small text-muted" style="max-width:200px;"><?= htmlspecialchars($row['comment']) ?></td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td class="text-center">
                                    <a href="album_reviews_manage.php?delete=<?= $row['id'] ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Delete this review permanently?')">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No reviews found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>