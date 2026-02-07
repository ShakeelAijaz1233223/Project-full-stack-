<?php
include "../config/db.php";

// 1. Delete Review Fix (Table name 'reviews' use kiya hai)
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");
    header("Location: video_reviews.php"); // Is file ka jo bhi naam aapne rakha ho
    exit();
}

// 2. Query Fix (Table 'reviews' aur column 'video_id' join kiya hai)
$query = "SELECT reviews.*, videos.title as v_title 
          FROM reviews 
          JOIN videos ON reviews.video_id = videos.id 
          ORDER BY reviews.id DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Video Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #080808; color: #fff; padding: 40px; font-family: 'Inter', sans-serif; }
        .table-container { 
            background: #111; 
            border-radius: 15px; 
            padding: 25px; 
            border: 1px solid #222; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .table { color: #fff; border-color: #222; }
        .stars { color: #ffca08; letter-spacing: 2px; }
        .btn-del { 
            background: rgba(255, 68, 68, 0.1); 
            color: #ff4444; 
            padding: 5px 10px; 
            border-radius: 6px; 
            transition: 0.3s;
        }
        .btn-del:hover { background: #ff4444; color: #fff; }
        .badge-date { font-size: 0.7rem; color: #555; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold m-0 text-uppercase" style="letter-spacing: 1px;">
                <i class="bi bi-person-badge me-2" style="color: #ff0055;"></i>Video Reviews
            </h3>
            <p class="text-muted small m-0">Manage all user feedback and ratings</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
        </a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr class="text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                        <th>Video Title</th>
                        <th>User Rating</th>
                        <th width="40%">Comment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white"><?= htmlspecialchars($row['v_title']) ?></div>
                                <div class="badge-date"><?= date('M d, Y', strtotime($row['created_at'])) ?></div>
                            </td>
                            <td>
                                <div class="stars">
                                    <?php 
                                    for($i=1; $i<=5; $i++) {
                                        echo ($i <= $row['rating']) ? '★' : '<span style="color: #333;">★</span>';
                                    } 
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-white-50 small" style="line-height: 1.4;">
                                    "<?= htmlspecialchars($row['comment']) ?>"
                                </div>
                            </td>
                            <td>
                                <a href="?del=<?= $row['id'] ?>" class="btn-del text-decoration-none" onclick="return confirm('Delete this feedback forever?')">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No reviews found in the database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>