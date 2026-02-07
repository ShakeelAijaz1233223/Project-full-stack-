<?php
// session_start(); // Agar dashboard use kar rahe hain to ise uncomment rakhein
include "../config/db.php";

// 1. Handle Delete Review - FIXED REDIRECTION
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if review exists before deleting
    $check = mysqli_query($conn, "SELECT id FROM album_reviews WHERE id = $id");
    
    if(mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM album_reviews WHERE id = $id");
        
        // DYNAMIC REDIRECT: Bina '/' ke direct file name use kiya hai
        header("Location: album_reviews_manage.php?status=deleted");
        exit();
    }
}

// 2. Fetch all reviews
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
        .table thead { background: #1a1a1a; color: var(--accent); }
        .album-img { width: 40px; height: 40px; border-radius: 5px; object-fit: cover; margin-right: 10px; border: 1px solid #333; }
        .stars { color: #ffca08; }
        .status-msg { font-size: 0.9rem; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .btn-action { padding: 5px 10px; border-radius: 6px; transition: 0.3s; }
        tr { border-bottom: 1px solid #1a1a1a; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">ALBUM <span style="color: var(--accent);">REVIEWS</span></h3>
        <a href="index.php" class="btn btn-outline-light btn-sm px-3">Dashboard</a>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-success status-msg bg-success text-white border-0 shadow-sm alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i> Review has been deleted successfully!
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-container shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                                        <img src="uploads/albums/<?= $row['cover'] ?>" class="album-img" onerror="this.src='https://via.placeholder.com/40'">
                                        <span class="fw-semibold"><?= htmlspecialchars($row['album_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="stars">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '<span style="color:#333">★</span>'; ?>
                                    </div>
                                    <small class="text-muted"><?= $row['rating'] ?>/5</small>
                                </td>
                                <td style="max-width: 300px;">
                                    <div class="text-truncate text-secondary" title="<?= htmlspecialchars($row['comment']) ?>">
                                        <?= htmlspecialchars($row['comment']) ?>
                                    </div>
                                </td>
                                <td><small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small></td>
                                <td class="text-center">
                                    <a href="album_reviews_manage.php?delete=<?= $row['id'] ?>" 
                                       class="btn btn-outline-danger btn-action" 
                                       onclick="return confirm('Do you really want to delete this review?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No reviews found in database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>