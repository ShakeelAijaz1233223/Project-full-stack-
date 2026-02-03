<?php
session_start();
include "../config/db.php";

// 1. DELETE LOGIC
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM album_reviews WHERE id = $id");
    header("Location: manage_reviews.php?msg=Deleted");
    exit();
}

// 2. UPDATE LOGIC
if (isset($_POST['update_review'])) {
    $id = (int)$_POST['review_id'];
    $rating = (int)$_POST['rating'];
    $text = mysqli_real_escape_string($conn, $_POST['review_text']);
    
    mysqli_query($conn, "UPDATE album_reviews SET rating='$rating', review_text='$text' WHERE id=$id");
    $msg = "Review updated successfully!";
}

// 3. FETCH REVIEWS (Joining with Albums table to see which album is reviewed)
$query = "SELECT r.*, a.title as album_title FROM album_reviews r 
          JOIN albums a ON r.album_id = a.id 
          ORDER BY r.created_at DESC";
$reviews = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Manager | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #080808; color: #eee; font-family: 'Inter', sans-serif; }
        .table { background: #121212; color: #eee; border-color: #333; }
        .table thead { background: #1a1a1a; }
        .btn-action { padding: 2px 8px; font-size: 0.8rem; }
        .rating-stars { color: #ffcc00; }
        .review-card { background: #121212; border: 1px solid #333; border-radius: 8px; padding: 20px; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-star-fill text-warning"></i> MANAGE <span style="color: #ff0055;">REVIEWS</span></h4>
        <a href="albums.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Back to Studio</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success bg-success text-white border-0"><?= $msg ?></div>
    <?php endif; ?>

    <div class="review-card">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Album</th>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($reviews)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['album_title']) ?></strong></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td class="rating-stars">
                        <?= str_repeat("⭐", $row['rating']) ?>
                    </td>
                    <td><small><?= htmlspecialchars($row['review_text']) ?></small></td>
                    <td><small class="text-muted"><?= date('d M, Y', strtotime($row['created_at'])) ?></small></td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Delete this review?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>

                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" class="modal-content bg-dark border-secondary">
                            <div class="modal-header border-secondary">
                                <h5 class="modal-title">Edit Review</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="review_id" value="<?= $row['id'] ?>">
                                <div class="mb-3">
                                    <label>Rating</label>
                                    <select name="rating" class="form-select bg-black text-white border-secondary">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == $row['rating'] ? 'selected' : '' ?>><?= $i ?> Stars</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Review Text</label>
                                    <textarea name="review_text" class="form-control bg-black text-white border-secondary" rows="4"><?= htmlspecialchars($row['review_text']) ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-secondary">
                                <button type="submit" name="update_review" class="btn btn-danger">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if(mysqli_num_rows($reviews) == 0): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No reviews found in database.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>