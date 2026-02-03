<?php
session_start();
include "../config/db.php";

// 1. Session Check
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetching current logged-in admin's ID
$adminEmail = $_SESSION['email'];
$currentAdminQuery = mysqli_query($conn, "SELECT id FROM admin_users WHERE email='$adminEmail'");
$currentAdminData = mysqli_fetch_assoc($currentAdminQuery);
$logged_in_id = $currentAdminData['id'] ?? 0;

/* ---- ACTIONS (Security Optimized) ---- */

// Delete Web User (From 'users' table)
if (isset($_GET['delete_web_user'])) {
    $user_id = (int)$_GET['delete_web_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$user_id");
    header("Location: user.php");
    exit();
}

// Block/Delete/Admin actions for 'admin_users' table
if (isset($_GET['block'])) {
    $user_id = (int)$_GET['block'];
    if ($user_id != $logged_in_id) { 
        mysqli_query($conn, "UPDATE admin_users SET status='blocked' WHERE id=$user_id");
    }
    header("Location: user.php");
    exit();
}

if (isset($_GET['unblock'])) {
    $user_id = (int)$_GET['unblock'];
    mysqli_query($conn, "UPDATE admin_users SET status='active' WHERE id=$user_id");
    header("Location: user.php");
    exit();
}

if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id != $logged_in_id) { 
        mysqli_query($conn, "DELETE FROM admin_users WHERE id=$user_id");
    }
    header("Location: user.php");
    exit();
}

if (isset($_GET['make_admin'])) {
    $user_id = (int)$_GET['make_admin'];
    mysqli_query($conn, "UPDATE admin_users SET role='admin' WHERE id=$user_id");
    header("Location: user.php");
    exit();
}

/* ---- Fetch Data ---- */
// 1. Web Users (New table)
$web_users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

// 2. Admin Staff
$admin_users_result = mysqli_query($conn, "SELECT id, name, email, role, status, avatar FROM admin_users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); min-height:100vh; padding:20px; }
        .main-card { background: rgba(255,255,255,0.95); border:none; border-radius:20px; overflow:hidden; box-shadow:0 15px 35px rgba(0,0,0,0.2); margin-bottom: 30px; }
        .header-gradient { background: linear-gradient(to right,#243b55,#141e30); padding:25px; color:white; }
        .table thead { background:#f8f9fa; }
        .table th { text-transform:uppercase; font-size:0.85rem; letter-spacing:1px; border:none; }
        .badge-admin { background:#00d2ff; color:white; }
        .badge-user { background:#ff9966; color:white; }
        .badge-active { background:#00b09b; color:white; }
        .badge-blocked { background:#ed213a; color:white; }
        .btn-action { border-radius:8px; transition:all 0.3s; font-weight:500; }
        .btn-action:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(0,0,0,0.1); }
        .tr-hover:hover { background-color:#f1f4ff !important; }
        .glass-footer { background:#f8f9fa; border-top:1px solid #eee; padding:20px; }
        .user-avatar { width:45px; height:45px; object-fit:cover; border-radius:50%; border:2px solid #667eea; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
        .letter-avatar { width:45px; height:45px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:white; background:#667eea; }
        .section-title { padding: 15px 25px; background: #eee; font-weight: 600; color: #333; }
    </style>
</head>
<body>
<div class="container">
    
    <div class="card main-card">
        <div class="header-gradient">
            <h2 class="mb-0"><i class="fas fa-users me-2"></i> Registered Web Users</h2>
            <small class="opacity-75">Users who registered via the website</small>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User Info</th>
                            <th>Phone/Address</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($web_users_result)) { ?>
                        <tr class="tr-hover">
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                            </td>
                            <td>
                                <div class="small"><b>P:</b> <?= htmlspecialchars($row['phone']) ?></div>
                                <div class="small text-truncate" style="max-width: 150px;"><b>A:</b> <?= htmlspecialchars($row['address']) ?></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-active"><?= strtoupper($row['status']) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="?delete_web_user=<?= $row['id'] ?>" onclick="return confirm('Delete this user?')" class="btn btn-outline-danger btn-sm btn-action"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card main-card">
        <div class="header-gradient d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0"><i class="fas fa-user-shield me-2"></i> Admin & Staff</h2>
                <small class="opacity-75">Management of dashboard administrators</small>
            </div>
            <span class="badge bg-light text-dark p-2 px-3">Logged in: <?= htmlspecialchars($_SESSION['email']) ?></span>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Details</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($admin_users_result)) { ?>
                        <tr class="tr-hover">
                            <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <?php 
                                        $imgPath = "uploads/" . $row['avatar'];
                                        if (!empty($row['avatar']) && file_exists($imgPath)): ?>
                                            <img src="<?= $imgPath ?>" class="user-avatar" alt="User Image">
                                        <?php else: ?>
                                            <div class="letter-avatar"><?= strtoupper(substr($row['name'],0,1)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge rounded-pill <?= $row['role']=='admin'?'badge-admin':'badge-user' ?>"><?= strtoupper($row['role']) ?></span></td>
                            <td><span class="badge rounded-pill <?= $row['status']=='active'?'badge-active':'badge-blocked' ?>"><?= strtoupper($row['status']) ?></span></td>
                            <td class="text-center">
                                <div class="btn-group gap-1">
                                    <?php if ($row['status']=='active') { ?>
                                        <a href="?block=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm btn-action"><i class="fas fa-lock"></i></a>
                                    <?php } else { ?>
                                        <a href="?unblock=<?= $row['id'] ?>" class="btn btn-outline-success btn-sm btn-action"><i class="fas fa-unlock"></i></a>
                                    <?php } ?>
                                    
                                    <?php if ($row['id'] != $logged_in_id) { ?>
                                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete admin?')" class="btn btn-outline-danger btn-sm btn-action"><i class="fas fa-trash-alt"></i></a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-footer d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-light border btn-action"><i class="fas fa-chevron-left me-2"></i> Return to Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-action"><i class="fas fa-sign-out-alt me-2"></i> Secure Logout</a>
        </div>
    </div>
</div>
</body>
</html>