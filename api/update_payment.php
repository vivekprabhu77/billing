<?php
require_once '../config/database.php';
require_once '../config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid security token.");
    }

    $conn = getDBConnection();
    
    $bill_id = intval($_POST['bill_id']);
    $amount_to_add = floatval($_POST['amount_to_add']);
    
    // Prepared statement for selection
    $sel_stmt = $conn->prepare("SELECT total_amount, paid_amount FROM payments WHERE bill_id = ?");
    $sel_stmt->bind_param("i", $bill_id);
    $sel_stmt->execute();
    $payment = $sel_stmt->get_result()->fetch_assoc();
    
    if (!$payment) {
        die("Payment record not found.");
    }

    $new_paid = $payment['paid_amount'] + $amount_to_add;
    $new_balance = $payment['total_amount'] - $new_paid;
    
    $status = 'Partial';
    if ($new_balance <= 0) {
        $status = 'Paid';
        $new_balance = 0;
    } elseif ($new_paid == 0) {
        $status = 'Pending';
    }
    
    $stmt = $conn->prepare("UPDATE payments SET paid_amount = ?, balance_amount = ?, status = ? WHERE bill_id = ?");
    $stmt->bind_param("ddsi", $new_paid, $new_balance, $status, $bill_id);
    
    if ($stmt->execute()) {
        header("Location: ../dashboard.php?page=view_bill&id=" . $bill_id);
    } else {
        logAppError("Error updating payment for bill $bill_id");
        die("Error updating payment.");
    }
    
    $conn->close();
}
?>
