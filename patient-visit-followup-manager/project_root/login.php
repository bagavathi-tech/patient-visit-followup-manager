<?php
// healthcare_system/login.php

require_once 'config/db.php';

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = escape($_POST['username']);
    $password = $_POST['password'];

    // Step 1: Check username exists
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        // Username wrong
        $error = "❌ Username not found!";
    } else {
        // Username correct → check password
        $user = $result->fetch_assoc();
        $hashed_password = hash('sha256', $password);

        if ($hashed_password !== $user['password']) {
            // Password wrong
            $error = "❌ Incorrect password!";
        } else {
            // Login success ✅
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Healthcare System</title>
    <link rel="stylesheet" href="includes/style.css">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0;">
    <div style="background: white; border-radius: 10px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); padding: 40px; width: 100%; max-width: 400px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #333; margin-bottom: 10px;">🏥 Healthcare System</h1>
            <p style="color: #666;">Please login to continue</p>
        </div>
        
        <?php if($error): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Username</label>
                <input type="text" name="username" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Password</label>
                <input type="password" name="password" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;" required>
            </div>
            
            <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer;">
                Login
            </button>
        </form>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 14px;">
            <h4 style="margin-top: 0; color: #333;">Demo Credentials:</h4>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Admin: admin / admin123</li>
                <li>Doctor: doctor / doctor123</li>
                <li>Staff: staff / staff123</li>
            </ul>
        </div>
    </div>
</body>
</html>