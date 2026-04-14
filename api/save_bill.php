<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDBConnection();
    $conn->begin_transaction();

    try {
        $bill_number = $_POST['bill_number'];
        $date = $_POST['date'];
        $customer_name = $_POST['customer_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $description = $_POST['description'];
        $grand_total = $_POST['grand_total'];

        // Insert into bills
        $stmt = $conn->prepare("INSERT INTO bills (bill_number, date, customer_name, phone, address, description, grand_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssd", $bill_number, $date, $customer_name, $phone, $address, $description, $grand_total);
        $stmt->execute();
        $bill_id = $conn->insert_id;

        // Insert bill items
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

        // Insert payment info
        $paid_amount = $_POST['paid_amount'];
        $balance_amount = $_POST['balance_amount'];
        $status = $_POST['payment_status'];

        $payment_stmt = $conn->prepare("INSERT INTO payments (bill_id, total_amount, paid_amount, balance_amount, status) VALUES (?, ?, ?, ?, ?)");
        $payment_stmt->bind_param("iddds", $bill_id, $grand_total, $paid_amount, $balance_amount, $status);
        $payment_stmt->execute();

        // Handle File Uploads
        if (!empty($_FILES['photos']['name'][0])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = time() . '_' . $_FILES['photos']['name'][$key];
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $media_stmt = $conn->prepare("INSERT INTO bill_media (bill_id, file_path) VALUES (?, ?)");
                    $path_to_save = 'uploads/' . $file_name;
                    $media_stmt->bind_param("is", $bill_id, $path_to_save);
                    $media_stmt->execute();
                }
            }
        }

        $conn->commit();
        header("Location: ../index.php?page=view_bill&id=" . $bill_id);
    } catch (Exception $e) {
        $conn->rollback();
        die("Error saving bill: " . $e->getMessage());
    }

    $conn->close();
}
?>
