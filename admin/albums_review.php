<?php
session_start();
include "../config/db.php";

// 1. DELETE LOGIC - Automatic URL detection ke sath
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $delete = mysqli_query($conn, "DELETE FROM album_reviews WHERE id = $id");
    
    if($delete) {
        // Yeh line automatic isi file par wapas bhej degi message ke sath
        $current_file = basename($_SERVER['PHP_SELF']);
        header("Location: $current_file?status=deleted");
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
        .album-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; border: 1px solid #333; }
        .stars { color: #ffca08; }
        .btn-delete { color: #ff4d4d; border: 1px solid #ff4d4d; padding: 6px 10px; border-radius: 6px; text-decoration: none; transition: 0.3s; }
        .btn-delete:hover { background: #ff4d4d; color: white; }
        .alert-success { background-color: #198754; color: white; border: none; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0 text-uppercase">Album <span style="color: var(--accent);">Reviews</span></h4>
        <a href="index.php" class="btn btn-outline-light btn-sm px-3">Dashboard</a>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> Review deleted successfully from database!
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-container shadow">
        <div class="table-responsive">
            <table class="table">
                <thead class="text-muted small">
                    <tr>
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
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../uploads/albums/<?= $row['cover'] ?>" class="album-img me-3" onerror="this.src='https://via.placeholder.com/50'">
                                        <span class="fw-semibold"><?= htmlspecialchars($row['album_name'] ?? 'Deleted Album') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="stars small">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '☆'; ?>
                                    </div>
                                    <span class="text-muted small"><?= $row['rating'] ?>/5</span>
                                </td>
                                <td class="small text-muted" style="max-width:250px;">
                                    <?= htmlspecialchars($row['comment']) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>?delete=<?= $row['id'] ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this review?')">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>