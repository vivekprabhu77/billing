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
    $conn->begin_transaction();

    try {
        $bill_id = intval($_POST['bill_id']);
        
        // Sanitize inputs
        $bill_number = htmlspecialchars(trim($_POST['bill_number'] ?? ''));
        $date = $_POST['date'] ?? date('Y-m-d');
        $customer_name = htmlspecialchars(trim($_POST['customer_name'] ?? ''));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $address = htmlspecialchars(trim($_POST['address'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $grand_total = floatval($_POST['grand_total'] ?? 0);

        // Update bills
        $stmt = $conn->prepare("UPDATE bills SET bill_number = ?, date = ?, customer_name = ?, phone = ?, address = ?, description = ?, grand_total = ? WHERE id = ?");
        $stmt->bind_param("ssssssdi", $bill_number, $date, $customer_name, $phone, $address, $description, $grand_total, $bill_id);
        $stmt->execute();

        // Update items (Delete and re-insert)
        $del_stmt = $conn->prepare("DELETE FROM bill_items WHERE bill_id = ?");
        $del_stmt->bind_param("i", $bill_id);
        $del_stmt->execute();

        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $item_stmt = $conn->prepare("INSERT INTO bill_items (bill_id, category, item_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['items'] as $item) {
                $category = htmlspecialchars($item['category'] ?? '');
                $item_name = htmlspecialchars($item['name'] ?? '');
                $quantity = floatval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);
                $total = floatval($item['total'] ?? 0);
                
                if (!empty($item_name)) {
                    $item_stmt->bind_param("issddd", $bill_id, $category, $item_name, $quantity, $price, $total);
                    $item_stmt->execute();
                }
            }
        }

        // Update payment info
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $balance_amount = floatval($_POST['balance_amount'] ?? 0);
        $status = $_POST['payment_status'] ?? 'Pending';

        $payment_stmt = $conn->prepare("UPDATE payments SET total_amount = ?, paid_amount = ?, balance_amount = ?, status = ? WHERE bill_id = ?");
        $payment_stmt->bind_param("dddsi", $grand_total, $paid_amount, $balance_amount, $status, $bill_id);
        $payment_stmt->execute();

        $conn->commit();
        header("Location: ../dashboard.php?page=view_bill&id=" . $bill_id);
    } catch (Exception $e) {
        $conn->rollback();
        logAppError("Error updating bill: " . $e->getMessage());
        die("An unexpected error occurred. Please try again later.");
    }

    $conn->close();
}
?>
