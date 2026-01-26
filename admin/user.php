<?php

include "db.php";

if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

/* ---- ACTIONS ---- */
if(isset($_GET['block'])){
    mysqli_query($conn,"UPDATE users SET status='blocked' WHERE id=".$_GET['block']);
}
if(isset($_GET['unblock'])){
    mysqli_query($conn,"UPDATE users SET status='active' WHERE id=".$_GET['unblock']);
}
if(isset($_GET['delete'])){
    mysqli_query($conn,"DELETE FROM users WHERE id=".$_GET['delete']);
}
if(isset($_GET['make_admin'])){
    mysqli_query($conn,"UPDATE users SET role='admin' WHERE id=".$_GET['make_admin']);
}
if(isset($_GET['make_user'])){
    mysqli_query($conn,"UPDATE users SET role='user' WHERE id=".$_GET['make_user']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">👑 Admin Panel – User Management</h3>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="35%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $data = mysqli_query($conn,"SELECT * FROM users");
                while($row = mysqli_fetch_assoc($data)){
                ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['email'] ?></td>

                        <td>
                            <span class="badge <?= $row['role']=='admin'?'bg-success':'bg-secondary' ?>">
                                <?= strtoupper($row['role']) ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge <?= $row['status']=='active'?'bg-primary':'bg-danger' ?>">
                                <?= strtoupper($row['status']) ?>
                            </span>
                        </td>

                        <td>
                            <?php if($row['status']=='active'){ ?>
                                <a href="?block=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Block</a>
                            <?php } else { ?>
                                <a href="?unblock=<?= $row['id'] ?>" class="btn btn-success btn-sm">Unblock</a>
                            <?php } ?>

                            <?php if($row['role']=='user'){ ?>
                                <a href="?make_admin=<?= $row['id'] ?>" class="btn btn-info btn-sm">Make Admin</a>
                            <?php } else { ?>
                                <a href="?make_user=<?= $row['id'] ?>" class="btn btn-secondary btn-sm">Make User</a>
                            <?php } ?>

                            <a href="?delete=<?= $row['id'] ?>" 
                               onclick="return confirm('Delete this user?')" 
                               class="btn btn-danger btn-sm">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

       <div class="card-footer text-end">
    <a href="dashboard.php" class="btn btn-secondary me-2">⬅ Back</a>
    <a href="logout.php" class="btn btn-outline-dark">Logout</a>
</div>
    </div>
</div>

</body>
</html>
