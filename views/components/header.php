<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Vignesh Decorators - Billing System</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php if(!isset($hide_nav) || !$hide_nav): ?>
<nav class="bottom-nav no-print">
    <a href="index.php?page=dashboard" class="nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    <a href="index.php?page=create_bill" class="nav-item">
        <i class="fas fa-plus-circle"></i>
        <span>New Bill</span>
    </a>
    <a href="index.php?page=bills" class="nav-item">
        <i class="fas fa-list"></i>
        <span>History</span>
    </a>
    <a href="index.php?page=reports" class="nav-item">
        <i class="fas fa-chart-bar"></i>
        <span>Reports</span>
    </a>
    <a href="index.php?action=logout" class="nav-item" style="color: #e74c3c;">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>
</nav>
<?php endif; ?>
<div class="container">
