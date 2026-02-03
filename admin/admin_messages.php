<?php
session_start();
include "../config/db.php";

/* ===============================
   ADMIN AUTH (SAME AS DASHBOARD)
================================ */
if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include "../config/db.php";

// Delete Message Logic
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM contact_messages WHERE id=$id");
    header("Location: admin_view_contacts.php");
    exit();
}

// Mark as Read Logic
if(isset($_GET['mark_read'])){
    $id = mysqli_real_escape_string($conn, $_GET['mark_read']);
    mysqli_query($conn, "UPDATE contact_messages SET status='read' WHERE id=$id");
    header("Location: admin_view_contacts.php");
    exit();
}

// Fetch Messages
$result = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Inbox | Studio Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent-color: #a363ff;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body { 
            background: #050508; 
            color: white; 
            font-family: 'Poppins', sans-serif; 
            padding: 40px 20px; 
            background-image: radial-gradient(circle at top right, #1a1a2e, #050508);
            min-height: 100vh;
        }

        .back-btn {
            background: var(--glass-bg);
            color: #fff;
            padding: 8px 20px;
            border-radius: 12px;
            text-decoration: none;
            border: 1px solid var(--glass-border);
            transition: 0.3s;
            margin-bottom: 30px;
            display: inline-block;
        }
        .back-btn:hover { background: var(--accent-color); color: #fff; transform: translateX(-5px); }

        .msg-box { 
            background: var(--glass-bg); 
            border: 1px solid var(--glass-border); 
            border-radius: 20px; 
            padding: 25px; 
            margin-bottom: 25px; 
            transition: 0.3s;
            backdrop-filter: blur(10px);
        }
        
        .msg-box:hover {
            border-color: rgba(163, 99, 255, 0.4);
            transform: translateY(-2px);
            background: rgba(255,255,255,0.05);
        }

        .unread { 
            border-left: 5px solid var(--accent-color); 
            background: rgba(163, 99, 255, 0.05); 
        }

        .status-badge {
            font-size: 10px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .bg-unread { background: var(--accent-color); color: white; }
        .bg-read { background: #28a745; color: white; }

        .message-content {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 15px;
            border: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
            line-height: 1.6;
            color: #ccc;
        }

        .btn-action {
            border-radius: 10px;
            font-size: 13px;
            padding: 8px 15px;
            font-weight: 500;
        }

        h2 { font-weight: 600; letter-spacing: 2px; color: #fff; }
    </style>
</head>
<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="dashboard.php" class="back-btn"><i class="fa fa-arrow-left me-2"></i>Dashboard</a>
            <h2 class="text-uppercase"><i class="fa fa-envelope-open-text me-3 text-info"></i>User Messages</h2>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="msg-box <?= ($row['status'] == 'unread') ? 'unread' : '' ?>">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <span class="status-badge <?= ($row['status'] == 'unread') ? 'bg-unread' : 'bg-read' ?>">
                                <?= $row['status'] ?>
                            </span>
                            <h5>
                                <i class="fa fa-user-circle me-2 text-muted"></i>
                                <?= htmlspecialchars($row['name']) ?> 
                                <small class="text-muted ms-2" style="font-size:13px;">&lt;<?= htmlspecialchars($row['email']) ?>&gt;</small>
                            </h5>
                            <p class="text-info small mb-3">
                                <i class="fa fa-clock me-1"></i> Received on: <?= date('M d, Y - h:i A', strtotime($row['created_at'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php if($row['status'] == 'unread'): ?>
                                <a href="?mark_read=<?= $row['id'] ?>" class="btn btn-success btn-action me-2">
                                    <i class="fa fa-check me-1"></i> Mark Read
                                </a>
                            <?php endif; ?>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-outline-danger btn-action" onclick="return confirm('Delete this message permanently?')">
                                <i class="fa fa-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>

                    <div class="message-content mt-3">
                        <i class="fa fa-quote-left me-2 text-muted opacity-50"></i>
                        <?= nl2br(htmlspecialchars($row['message'])) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Your inbox is empty.</h4>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>