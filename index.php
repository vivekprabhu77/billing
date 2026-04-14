<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Protection
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}

requireLogin();


$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Simple routing
switch ($page) {
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'create_bill':
        include 'views/create_bill.php';
        break;
    case 'edit_bill':
        include 'views/edit_bill.php';
        break;
    case 'view_bill':
        include 'views/view_bill.php';
        break;
    case 'bills':
        include 'views/history.php';
        break;
    case 'reports':
        include 'views/reports.php';
        break;
    default:
        include 'views/dashboard.php';
        break;
}

// Minimalist Controller Logic for Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_bill':
                // Logic to save bill will be here or in a separate handler
                break;
        }
    }
}
?>
