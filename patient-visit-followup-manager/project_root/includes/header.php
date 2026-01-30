<?php
require_once __DIR__ .'/../config/db.php';
$base_url = "/patient-visit-followup-manager/project_root/";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare System - <?php echo $page_title ?? 'Dashboard'; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f7fa; color: #333; line-height: 1.6; }
        .navbar { background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white; padding: 15px 30px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .navbar-container { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .navbar-brand { font-size: 24px; font-weight: bold; text-decoration: none; color: white; }
        .navbar-menu { display: flex; gap: 20px; }
        .navbar-menu a { color: white; text-decoration: none; padding: 8px 15px; border-radius: 4px; transition: background 0.3s; }
        .navbar-menu a:hover { background: rgba(255, 255, 255, 0.2); }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); }
        .btn { display: inline-block; padding: 10px 20px; background: #0066cc; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #004d99; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #f8f9fa; font-weight: 600; }
        table tr:hover { background-color: #f5f5f5; }
        .status-overdue { color: #dc3545; font-weight: bold; }
        .status-upcoming { color: #28a745; font-weight: bold; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <nav class="navbar">
    <div class="topbar">
    <div class="topbar-left">
        🏥 <strong>Healthcare System</strong>
    </div>

    <div class="topbar-center">
        <a href="<?php echo $base_url; ?>index.php">Dashboard</a>
        <a href="<?php echo $base_url; ?>patients/list.php">Patients</a>
        <a href="<?php echo $base_url; ?>visits/list.php">Visits</a>
        <a href="<?php echo $base_url; ?>reports/summary.php">Reports</a>
    </div>

    <div class="topbar-right">
        <?php if(isset($_SESSION['username'])): ?>
            <span class="user-info">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            </span>
            <a href="<?php echo $base_url; ?>logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>login.php" class="btn">Login</a>
        <?php endif; ?>
    </div>
</div>
    </nav>
    <div class="container">