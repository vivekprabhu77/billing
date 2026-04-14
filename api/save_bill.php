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
        // Sanitize inputs
        $bill_number = htmlspecialchars(trim($_POST['bill_number'] ?? ''));
        $date = $_POST['date'] ?? date('Y-m-d');
        $customer_name = htmlspecialchars(trim($_POST['customer_name'] ?? ''));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $address = htmlspecialchars(trim($_POST['address'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $grand_total = floatval($_POST['grand_total'] ?? 0);

        // Insert into bills
        $stmt = $conn->prepare("INSERT INTO bills (bill_number, date, customer_name, phone, address, description, grand_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssd", $bill_number, $date, $customer_name, $phone, $address, $description, $grand_total);
        $stmt->execute();
        $bill_id = $conn->insert_id;

        // Insert bill items
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

        // Insert payment info
        $paid_amount = floatval($_POST['paid_amount'] ?? 0);
        $balance_amount = floatval($_POST['balance_amount'] ?? 0);
        $status = $_POST['payment_status'] ?? 'Pending';

        $payment_stmt = $conn->prepare("INSERT INTO payments (bill_id, total_amount, paid_amount, balance_amount, status) VALUES (?, ?, ?, ?, ?)");
        $payment_stmt->bind_param("iddds", $bill_id, $grand_total, $paid_amount, $balance_amount, $status);
        $payment_stmt->execute();

        // Handle File Uploads (Security)
        if (!empty($_FILES['photos']['name'][0])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $allowed_types = ['jpg', 'jpeg', 'png'];
            $max_size = 2 * 1024 * 1024; // 2MB

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if (empty($tmp_name)) continue;

                $file_name = $_FILES['photos']['name'][$key];
                $file_size = $_FILES['photos']['size'][$key];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (in_array($file_ext, $allowed_types) && $file_size <= $max_size) {
                    $new_file_name = uniqid('bill_', true) . '.' . $file_ext;
                    $target_file = $upload_dir . $new_file_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $media_stmt = $conn->prepare("INSERT INTO bill_media (bill_id, file_path) VALUES (?, ?)");
                        $path_to_save = 'uploads/' . $new_file_name;
                        $media_stmt->bind_param("is", $bill_id, $path_to_save);
                        $media_stmt->execute();
                    }
                }
            }
        }

        $conn->commit();
        header("Location: ../dashboard.php?page=view_bill&id=" . $bill_id);
    } catch (Exception $e) {
        $conn->rollback();
        logAppError("Error saving bill: " . $e->getMessage());
        die("An unexpected error occurred. Please try again later.");
    }

    $conn->close();
}
?>
