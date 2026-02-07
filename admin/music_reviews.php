<?php
include "../config/db.php";

// 1. DYNAMIC DELETE LOGIC (Har file name par kaam karega)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_query = mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");
    
    if($delete_query) {
        // $_SERVER['PHP_SELF'] se file name ka issue khatam ho jayega
        $current_page = basename($_SERVER['PHP_SELF']);
        header("Location: $current_page?status=deleted");
        exit();
    }
}

// 2. FETCH REVIEWS
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Manage Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --bg-dark: #080808; --card-bg: #121212; --accent: #ff0055; }
        body { background: var(--bg-dark); color: #eee; font-family: 'Inter', sans-serif; padding: 30px; }
        
        .admin-card { background: var(--card-bg); border: 1px solid #222; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .table { color: #fff; margin-bottom: 0; }
        .table thead { background: #1a1a1a; border-bottom: 2px solid #333; }
        .table th { color: var(--accent); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; padding: 15px; }
        .table td { padding: 15px; border-bottom: 1px solid #1f1f1f; vertical-align: middle; }
        
        .rating-star { color: #ffca08; font-size: 0.9rem; }
        .btn-delete { 
            color: #ff4444; 
            border: 1px solid #ff4444; 
            padding: 5px 12px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-size: 0.85rem;
            transition: 0.3s;
        }
        .btn-delete:hover { background: #ff4444; color: #fff; box-shadow: 0 0 10px rgba(255, 68, 68, 0.3); }
        
        /* Success Alert Style */
        .status-alert { 
            background: #1a1a1a; 
            border-left: 4px solid #28a745; 
            color: #fff; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0"><i class="bi bi-star-fill text-warning me-2"></i> USER <span style="color: var(--accent);">REVIEWS</span></h4>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm px-3 border-secondary text-secondary">Back to Dashboard</a>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="status-alert d-flex align-items-center shadow">
                <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                <div>
                    <strong>Record Removed!</strong><br>
                    <small class="text-secondary">The review has been permanently deleted from the database.</small>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Music Track</th>
                            <th>Rating</th>
                            <th>User Comment</th>
                            <th>Posted Date</th>
                            <th class="text-center">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($res) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['music_title']) ?></div>
                                    <small class="text-muted">ID: #<?= $row['id'] ?></small>
                                </td>
                                <td>
                                    <div class="rating-star">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '<span style="color:#333">★</span>'; ?>
                                    </div>
                                    <span class="badge bg-dark border border-secondary mt-1"><?= $row['rating'] ?>/5</span>
                                </td>
                                <td>
                                    <div style="max-width: 300px; font-size: 0.9rem;" class="text-secondary">
                                        "<?= htmlspecialchars($row['comment']) ?>"
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i> <?= date('d M, Y', strtotime($row['created_at'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>?delete=<?= $row['id'] ?>" 
                                       onclick="return confirm('Confirm deletion? This cannot be undone.')" 
                                       class="btn-delete">
                                        <i class="bi bi-trash3-fill me-1"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox-fill fs-1 d-block mb-2"></i>
                                    No reviews found in records.
                                </td>
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