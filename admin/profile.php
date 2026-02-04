<?php
session_start(); // Fixed: Session start is mandatory
include "../config/db.php";

/* ---- LOGIN CHECK ---- */
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

/* ---- GET USER DATA FROM admin_users ---- */
$email = $_SESSION['email'];
$user_query = mysqli_query($conn, "SELECT * FROM admin_users WHERE email='$email'"); // Changed 'users' to 'admin_users'
$user = mysqli_fetch_assoc($user_query);

/* ---- HANDLE MESSAGES ---- */
$success = "";
$error = "";

// Profile Update Logic
if(isset($_POST['update_profile'])){
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

    $avatar_name = $user['avatar'] ?? 'default.png';
    
    // Avatar Upload Fix
    if(isset($_FILES['avatar']) && $_FILES['avatar']['name'] != ''){
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar_name = 'avatar_'.$user['id'].'_'.time().'.'.$ext;
        
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        
        if(move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/'.$avatar_name)){
            // Purani file delete karne ka logic agar wo default na ho
            if($user['avatar'] != 'default.png' && file_exists('uploads/'.$user['avatar'])){
                unlink('uploads/'.$user['avatar']);
            }
        }
    }

    $update_sql = "UPDATE admin_users SET name='$name', phone='$phone', address='$address', avatar='$avatar_name' WHERE id=".$user['id'];
    
    if(mysqli_query($conn, $update_sql)){
        $success = "Profile updated successfully!";
        // Refresh local data
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admin_users WHERE email='$email'"));
    } else {
        $error = "Update failed: " . mysqli_error($conn);
    }
}

// Password Change Logic
if(isset($_POST['change_password'])){
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Fixed: Password match check as per your DB dump (plain text)
    if($current === $user['password']){
        if($new === $confirm && !empty($new)){
            mysqli_query($conn, "UPDATE admin_users SET password='$new' WHERE id=".$user['id']);
            $success = "Security Protocol Updated: Password changed!";
        } else {
            $error = "New passwords do not match!";
        }
    } else {
        $error = "Current Master Key (Password) is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIP Account Settings | Obsidian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --accent-color: #a363ff; 
            --accent-gradient: linear-gradient(135deg, #a363ff 0%, #6030ff 100%);
            --glass-white: rgba(255, 255, 255, 0.02);
            --glass-border: rgba(255, 255, 255, 0.07);
            --bg-dark: #050508;
            --card-bg: rgba(10, 10, 15, 0.7);
        }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(163, 99, 255, 0.08) 0%, transparent 35%),
                radial-gradient(circle at 100% 100%, rgba(96, 48, 255, 0.08) 0%, transparent 35%);
            min-height: 100vh;
            color: #d1d1d1;
            padding: 80px 0;
        }

        #pageLoader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-dark); display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }
        .loader-bar { width: 120px; height: 1px; background: rgba(255,255,255,0.05); position: relative; overflow: hidden; }
        .loader-bar::after { content: ''; position: absolute; left: -100%; width: 100%; height: 100%; background: var(--accent-color); animation: slide 1.2s cubic-bezier(0.4, 0, 0.2, 1) infinite; }
        @keyframes slide { to { left: 100%; } }

        .back-nav { position: fixed; top: 30px; left: 30px; z-index: 100; }
        .back-link { 
            color: #666; text-decoration: none; font-size: 11px; font-weight: 600;
            background: var(--glass-white); padding: 12px 24px; border-radius: 12px;
            border: 1px solid var(--glass-border); transition: 0.4s; text-transform: uppercase; letter-spacing: 2px;
        }
        .back-link:hover { color: var(--accent-color); border-color: var(--accent-color); }

        .settings-grid {
            max-width: 1100px; width: 95%; margin: auto;
            display: grid; grid-template-columns: 340px 1fr; gap: 40px;
        }

        .profile-side {
            background: var(--card-bg); backdrop-filter: blur(40px);
            border: 1px solid var(--glass-border); border-radius: 40px;
            padding: 50px 30px; text-align: center; height: fit-content;
        }
        .avatar-box { position: relative; width: 150px; height: 150px; margin: 0 auto 30px; }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; border-radius: 50px; border: 1px solid var(--glass-border); padding: 5px; }
        
        .upload-overlay {
            position: absolute; bottom: 5px; right: 5px;
            background: var(--accent-gradient); width: 40px; height: 40px;
            border-radius: 15px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 4px solid #0a0a0f; color: #fff;
        }

        .form-side {
            background: var(--card-bg); border-radius: 40px;
            border: 1px solid var(--glass-border); padding: 50px;
        }

        .section-title { font-size: 14px; font-weight: 600; margin-bottom: 35px; display: flex; align-items: center; text-transform: uppercase; letter-spacing: 3px; color: #666; }
        .section-title i { color: var(--accent-color); margin-right: 15px; }

        .input-group-custom { margin-bottom: 30px; }
        .input-group-custom label { display: block; font-size: 10px; color: #444; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; font-weight: 700; }
        .input-group-custom input, .input-group-custom textarea {
            width: 100%; background: rgba(255,255,255,0.01); border: 1px solid #15151a;
            border-radius: 18px; padding: 16px 24px; color: #fff; transition: 0.4s;
        }
        .input-group-custom input:focus { border-color: var(--accent-color); outline: none; background: rgba(163, 99, 255, 0.03); }

        .btn-update {
            background: var(--accent-gradient);
            border: none; color: #fff; padding: 16px 40px; border-radius: 20px;
            font-weight: 600; width: 100%; transition: 0.4s; font-size: 12px; text-transform: uppercase; letter-spacing: 2px;
        }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(96, 48, 255, 0.3); }

        .alert-custom { background: rgba(163, 99, 255, 0.1); border: 1px solid var(--accent-color); color: var(--accent-color); padding: 15px; border-radius: 15px; margin-bottom: 25px; text-align: center; }
        .alert-error { background: rgba(255, 50, 50, 0.1); border: 1px solid #ff3232; color: #ff3232; padding: 15px; border-radius: 15px; margin-bottom: 25px; text-align: center; }

        @media (max-width: 992px) { .settings-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div id="pageLoader"><div class="loader-bar"></div></div>

<nav class="back-nav">
    <a href="dashboard.php" class="back-link"><i class="fa fa-arrow-left-long me-2"></i> Exit Settings</a>
</nav>

<div class="settings-grid">
    <div class="profile-side">
        <form method="post" enctype="multipart/form-data">
            <div class="avatar-box">
                <img src="uploads/<?= !empty($user['avatar']) ? $user['avatar'] : 'default.png' ?>" id="preview">
                <label for="avatar_upload" class="upload-overlay">
                    <i class="fa fa-plus"></i>
                </label>
                <input type="file" id="avatar_upload" name="avatar" hidden onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
            </div>
            <h3 class="mb-1" style="font-weight: 600; color: #fff;"><?= htmlspecialchars($user['name']) ?></h3>
            <div class="mb-4"><span style="background: rgba(163, 99, 255, 0.1); color: var(--accent-color); padding: 5px 15px; border-radius: 30px; font-size: 10px; font-weight: 700;">ELITE ACCESS</span></div>
            
            <button type="submit" name="update_profile" class="btn btn-update">Save Profile Image</button>
        </form>
        <hr style="opacity: 0.1;">
        <div class="small text-muted mb-1 text-uppercase" style="font-size: 9px;">Account ID</div>
        <div class="small text-white opacity-50 mb-4"><?= $user['email'] ?></div>
        <a href="logout.php" class="text-danger text-decoration-none small fw-bold">TERMINATE SESSION</a>
    </div>

    <div class="form-side">
        <?php if($success): ?>
            <div class="alert-custom"><i class="fa fa-check-circle me-2"></i> <?= $success ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert-error"><i class="fa fa-exclamation-triangle me-2"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="section-title"><i class="fa fa-sliders"></i> Master Identity</div>
            <div class="row">
                <div class="col-md-6 input-group-custom">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="col-md-6 input-group-custom">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
            </div>
            <div class="input-group-custom">
                <label>Operational HQ Address</label>
                <textarea name="address" rows="2" style="resize: none;"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <div class="text-end">
                <button type="submit" name="update_profile" class="btn btn-update w-auto px-5">Sync Account</button>
            </div>
        </form>

        <hr style="opacity: 0.1; margin: 40px 0;">

        <form method="post">
            <div class="section-title"><i class="fa fa-vault"></i> Security Protocol</div>
            <div class="input-group-custom">
                <label>Current Master Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="row">
                <div class="col-md-6 input-group-custom">
                    <label>New Secret Key</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="col-md-6 input-group-custom">
                    <label>Repeat Key</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>
            <button type="submit" name="change_password" class="btn btn-update" style="background: rgba(255,255,255,0.03); border: 1px solid #222;">
                Recalibrate Security
            </button>
        </form>
    </div>
</div>

<script>
    window.addEventListener('load', () => {
        const loader = document.getElementById('pageLoader');
        setTimeout(() => { 
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.display = 'none'; }, 600);
        }, 400);
    });
</script>

</body>
</html>