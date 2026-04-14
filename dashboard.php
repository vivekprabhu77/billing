<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Protection
requireLogin();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Routing
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
?>
