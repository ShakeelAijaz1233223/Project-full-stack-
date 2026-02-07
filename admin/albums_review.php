<?php
session_start();
include "../config/db.php";

// 1. DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Database se delete karne ki query
    $delete_query = "DELETE FROM album_reviews WHERE id = $id";
    
    if(mysqli_query($conn, $delete_query)) {
        // SUCCESS: Redirect back with status
        header("Location: album_reviews_manage.php?status=deleted");
        exit();
    } else {
        // ERROR: Agar query fail ho jaye
        die("Error deleting record: " . mysqli_error($conn));
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
        .album-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; border: 1px solid #333; }
        .stars { color: #ffca08; }
        .btn-delete { color: #ff4d4d; border: 1px solid #ff4d4d; padding: 6px 10px; border-radius: 6px; text-decoration: none; transition: 0.3s ease; }
        .btn-delete:hover { background: #ff4d4d; color: white; box-shadow: 0 0 10px rgba(255, 77, 77, 0.4); }
        .alert-custom { background: #1a1a1a; border-left: 4px solid #28a745; color: #fff; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-uppercase">Album <span style="color: var(--accent);">Reviews</span></h4>
        <a href="index.php" class="btn btn-outline-light btn-sm px-3">Dashboard</a>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-custom alert-dismissible fade show shadow" role="alert">
            <i class="bi bi-check-circle-fill text-success me-2"></i> 
            <strong>Success!</strong> Review has been permanently removed from the database.
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-container shadow">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="text-muted small">
                    <tr class="border-secondary">
                        <th>ALBUM</th>
                        <th>RATING</th>
                        <th>COMMENT</th>
                        <th>DATE</th>
                        <th class="text-center">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-bottom border-secondary">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/albums/<?= $row['cover'] ?>" class="album-img me-3">
                                        <span class="fw-semibold"><?= htmlspecialchars($row['album_name'] ?? 'Unknown Album') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="stars small">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                                    </div>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= $row['rating'] ?>/5</span>
                                </td>
                                <td class="small text-muted" style="max-width:250px;">
                                    <?= nl2br(htmlspecialchars($row['comment'])) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="text-center">
                                    <a href="album_review.php?delete=<?= $row['id'] ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Are you sure? This will delete the review from the database.');">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No reviews available in the database.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>