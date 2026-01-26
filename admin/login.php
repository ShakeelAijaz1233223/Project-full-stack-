<?php
include "db.php";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['email'] = $email;
        header("Location: dashboard.php");
    } else {
        echo "<script>alert('Invalid Login');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /* background: url('bg.jpg') no-repeat center center; */
            background-size: cover;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
        }

        .card {
            background: rgba(30, 30, 30, 0.9);
            /* border-radius: 20px; */
            padding: 40px;
            box-shadow: 0 0 25px rgba(198, 201, 199, 0.4);
            width: 100%;
        }

        h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #d9e9e1;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group .form-label {
            position: absolute;
            top: -10px;
            left: 15px;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            /* background-colo  r: rgba(30, 30, 30, 0.8); */
            padding: 0 5px;
            z-index: 2;
        }

        .form-control {
            background: rgba(42, 42, 42, 0.9);
            border: 1px solid #444;
            color: #fff;
            /* border-radius: 12px; */
            padding: 12px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: none;
            box-shadow: 0 0 8px #223020;
            background: rgba(42, 42, 42, 0.95);
            color: #fff;
        }

        .btn-primary {
            width: 100%;
            background: #0a0808ab;
            border: none;
            padding: 12px;
            font-weight: bold;
            font-size: 16px;
            /* border-radius: 12px; */
            transition: all 0.3s;
            color: #9e9a9aab;
        }

        .btn-primary:hover {
            background: #171817;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <h3><i class="fa fa-user-circle"></i> Login</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    </div>
                    <button name="login" class="btn btn-primary"><i class="fa fa-right-to-bracket"></i> Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
