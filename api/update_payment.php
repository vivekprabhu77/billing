<?php
require_once '../config/database.php';
require_once '../config/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    
    $bill_id = $_POST['bill_id'];
    $amount_to_add = floatval($_POST['amount_to_add']);
    
    $payment_query = $conn->query("SELECT * FROM payments WHERE bill_id = $bill_id");
    $payment = $payment_query->fetch_assoc();
    
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
        header("Location: ../index.php?page=view_bill&id=" . $bill_id);
    } else {
        die("Error updating payment.");
    }
    
    $conn->close();
}
?>
