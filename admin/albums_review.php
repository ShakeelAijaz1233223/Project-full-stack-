<?php
session_start();
include "../config/db.php";

// 1. Handle Delete Review - ISME KOI CHANGE NAHI HAI
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM album_reviews WHERE id = $id";
    if(mysqli_query($conn, $delete_query)) {
        header("Location: album_reviews_manage.php?status=deleted");
        exit();
    }
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
        .table thead { background: #1a1a1a; color: var(--accent); }
        .album-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; margin-right: 12px; border: 1px solid #333; }
        .stars { color: #ffca08; letter-spacing: 2px; }
        .btn-action { padding: 5px 10px; font-size: 0.9rem; border-radius: 6px; transition: 0.3s; }
        .status-msg { font-size: 0.85rem; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .table-hover tbody tr:hover { background-color: rgba(255, 0, 85, 0.05); }
        tr { border-bottom: 1px solid #1a1a1a; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">ALBUM <span style="color: var(--accent);">REVIEWS</span></h3>
        <a href="index.php" class="btn btn-outline-light btn-sm px-3"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
        <div class="alert alert-danger status-msg bg-danger text-white border-0 shadow">
            <i class="bi bi-check-circle me-2"></i> Review has been removed from the database.
        </div>
    <?php endif; ?>

    <div class="table-container shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="border-0">Album Details</th>
                        <th class="border-0">User Rating</th>
                        <th class="border-0">User Comment</th>
                        <th class="border-0">Posted Date</th>
                        <th class="border-0 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/albums/<?= $row['cover'] ?>" class="album-img">
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($row['album_name']) ?></div>
                                            <small class="text-muted">ID: #<?= $row['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="stars">
                                        <?php 
                                        for($i=1; $i<=5; $i++) {
                                            echo ($i <= $row['rating']) ? '★' : '<span style="color:#333">★</span>'; 
                                        }
                                        ?>
                                    </div>
                                    <small class="badge bg-dark text-warning border border-secondary mt-1"><?= $row['rating'] ?> / 5</small>
                                </td>
                                <td>
                                    <div style="max-width: 250px; font-size: 0.9rem;" title="<?= htmlspecialchars($row['comment']) ?>">
                                        <?= nl2br(htmlspecialchars($row['comment'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i> <?= date('M d, Y', strtotime($row['created_at'])) ?><br>
                                        <i class="bi bi-clock me-1"></i> <?= date('h:i A', strtotime($row['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="album_reviews_manage.php?delete=<?= $row['id'] ?>" 
                                       class="btn btn-outline-danger btn-action" 
                                       onclick="return confirm('Do you really want to delete this review? This action cannot be undone.');">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-chat-left-dots text-muted display-4 d-block mb-3"></i>
                                <p class="text-muted m-0">No reviews found in the studio records.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>