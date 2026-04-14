<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $conn->begin_transaction();

    try {
        $bill_id = $_POST['bill_id'];
        $bill_number = $_POST['bill_number'];
        $date = $_POST['date'];
        $customer_name = $_POST['customer_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $description = $_POST['description'];
        $grand_total = $_POST['grand_total'];

        // Update bills
        $stmt = $conn->prepare("UPDATE bills SET bill_number = ?, date = ?, customer_name = ?, phone = ?, address = ?, description = ?, grand_total = ? WHERE id = ?");
        $stmt->bind_param("ssssssdi", $bill_number, $date, $customer_name, $phone, $address, $description, $grand_total, $bill_id);
        $stmt->execute();

        // Update items (Delete and re-insert for simplicity in this case)
        $conn->query("DELETE FROM bill_items WHERE bill_id = $bill_id");
        if (isset($_POST['items'])) {
            $item_stmt = $conn->prepare("INSERT INTO bill_items (bill_id, category, item_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['items'] as $item) {
                $category = $item['category'];
                $item_name = $item['name'];
                $quantity = floatval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);
                $total = floatval($item['total'] ?? 0);
                $item_stmt->bind_param("issddd", $bill_id, $category, $item_name, $quantity, $price, $total);
                $item_stmt->execute();
            }
        }

        // Update payment info
        $paid_amount = $_POST['paid_amount'];
        $balance_amount = $_POST['balance_amount'];
        $status = $_POST['payment_status'];

        $payment_stmt = $conn->prepare("UPDATE payments SET total_amount = ?, paid_amount = ?, balance_amount = ?, status = ? WHERE bill_id = ?");
        $payment_stmt->bind_param("dddsi", $grand_total, $paid_amount, $balance_amount, $status, $bill_id);
        $payment_stmt->execute();

        $conn->commit();
        header("Location: ../index.php?page=view_bill&id=" . $bill_id);
    } catch (Exception $e) {
        $conn->rollback();
        die("Error updating bill: " . $e->getMessage());
    }

    $conn->close();
}
?>
