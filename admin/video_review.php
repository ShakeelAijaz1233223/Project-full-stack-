<?php
include "../config/db.php";

// 1. FIXED DELETE LOGIC - Automatic Page Detection
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $delete = mysqli_query($conn, "DELETE FROM video_reviews WHERE id = $id");
    
    if($delete) {
        // Yeh line automatic isi file par wapas bhej degi, chahe file ka naam kuch bhi ho
        $self = basename($_SERVER['PHP_SELF']);
        header("Location: $self?msg=deleted");
        exit();
    }
}

// 2. Fetch Video Reviews with Video Titles
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Video Feedback Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --accent: #ff0055; --bg: #080808; --card: #121212; --border: #222; }
        body { background: var(--bg); color: #fff; font-family: 'Inter', sans-serif; padding-top: 50px; }
        
        .glass-card { 
            background: var(--card); border: 1px solid var(--border); border-radius: 16px; 
            padding: 0; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }
        
        .table { color: #ddd; margin-bottom: 0; }
        .table thead { background: #1a1a1a; }
        .table thead th { 
            color: var(--accent); font-size: 0.75rem; text-transform: uppercase; 
            letter-spacing: 1px; padding: 18px; border-bottom: 1px solid var(--border);
        }
        .table td { padding: 18px; border-bottom: 1px solid #1a1a1a; vertical-align: middle; }
        
        .stars { color: #ffca08; font-size: 0.85rem; }
        .empty-star { color: #333; }
        
        .btn-del { 
            width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255, 0, 85, 0.1); color: #ff0055; border-radius: 10px;
            text-decoration: none; transition: 0.3s; border: 1px solid rgba(255, 0, 85, 0.2);
        }
        .btn-del:hover { background: #ff0055; color: #fff; transform: translateY(-2px); }
        
        .status-alert {
            background: #1a1a1a; border-left: 4px solid #28a745; color: #fff;
            padding: 15px 20px; border-radius: 12px; margin-bottom: 25px;
            display: flex; align-items: center; justify-content: space-between;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="bi bi-play-circle-fill text-danger me-2"></i>VIDEO <span style="color:var(--accent)">REVIEWS</span></h2>
            <p class="text-muted mb-0 mt-1">Moderation panel for visual content feedback</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm px-4 rounded-pill border-secondary">
            <i class="bi bi-arrow-left me-2"></i>Dashboard
        </a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="status-alert shadow-sm alert dismissible fade show">
            <div>
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                <strong>Action Successful:</strong> Review has been wiped from database.
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 25%;">Video Project</th>
                        <th style="width: 15%;">Rating</th>
                        <th style="width: 40%;">User Feedback</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 10%;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white"><?= htmlspecialchars($row['v_title']) ?></div>
                                <small class="text-muted" style="font-size: 0.7rem;">REF-ID: #<?= $row['id'] ?></small>
                            </td>
                            <td>
                                <div class="stars">
                                    <?php 
                                    for($i=1; $i<=5; $i++) {
                                        echo ($i <= $row['rating']) ? '★' : '<span class="empty-star">★</span>';
                                    }
                                    ?>
                                </div>
                                <span class="badge bg-dark text-secondary mt-1" style="font-size: 0.65rem;"><?= $row['rating'] ?> / 5</span>
                            </td>
                            <td>
                                <div class="text-muted small" style="line-height: 1.6; max-width: 350px;">
                                    "<?= htmlspecialchars($row['comment']) ?>"
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary small"><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?= basename($_SERVER['PHP_SELF']) ?>?del=<?= $row['id'] ?>" 
                                   class="btn-del" 
                                   onclick="return confirm('Confirm permanent deletion?')">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-chat-left-dots text-muted fs-1 d-block mb-3"></i>
                                <span class="text-muted">No video reviews found in the system.</span>
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