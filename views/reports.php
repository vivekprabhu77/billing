<?php
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

$conn = getDBConnection();

// Get current month and year
$month = intval(isset($_GET['month']) ? $_GET['month'] : date('m'));
$year = intval(isset($_GET['year']) ? $_GET['year'] : date('Y'));

// Month Name
$month_name = date("F", mktime(0, 0, 0, $month, 10));

// Category wise breakdown for the month
$stmt1 = $conn->prepare("
    SELECT category, SUM(total) as total 
    FROM bill_items 
    INNER JOIN bills ON bill_items.bill_id = bills.id 
    WHERE MONTH(bills.date) = ? AND YEAR(bills.date) = ?
    GROUP BY category
");
$stmt1->bind_param("ii", $month, $year);
$stmt1->execute();
$category_report = $stmt1->get_result();

$categories = [];
while($row = $category_report->fetch_assoc()) {
    $categories[$row['category']] = $row['total'];
}

// Financial Summary for the month
$stmt2 = $conn->prepare("
    SELECT 
        SUM(total_amount) as total_revenue,
        SUM(paid_amount) as total_received,
        SUM(balance_amount) as total_pending
    FROM payments 
    INNER JOIN bills ON payments.bill_id = bills.id 
    WHERE MONTH(bills.date) = ? AND YEAR(bills.date) = ?
");
$stmt2->bind_param("ii", $month, $year);
$stmt2->execute();
$summary = $stmt2->get_result()->fetch_assoc();

include __DIR__ . '/components/header.php';
?>

<h2 style="margin-bottom: 20px;">Monthly Report</h2>

<form method="GET" action="dashboard.php" style="margin-bottom: 30px;">
    <input type="hidden" name="page" value="reports">
    <div style="display: flex; gap: 10px;">
        <select name="month" style="flex: 1; padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <?php for($m=1; $m<=12; $m++): ?>
                <option value="<?php echo sprintf("%02d", $m); ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>
        <select name="year" style="flex: 1; padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            <?php for($y=2024; $y<=2030; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" style="padding: 10px 20px; background: var(--accent-color); color: white; border: none; border-radius: 4px;">GO</button>
    </div>
</form>

<div class="bill-paper" style="min-height: auto;">
    <h3>Income Breakdown - <?php echo $month_name . ' ' . $year; ?></h3>
    <hr style="margin: 15px 0;">
    
    <div style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #eee;">
            <span>Decoration</span>
            <strong>₹<?php echo number_format($categories['Decoration'] ?? 0, 2); ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #eee;">
            <span>Sound</span>
            <strong>₹<?php echo number_format($categories['Sound'] ?? 0, 2); ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #eee;">
            <span>Lighting</span>
            <strong>₹<?php echo number_format($categories['Lighting'] ?? 0, 2); ?></strong>
        </div>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span>Total Billed:</span>
            <strong>₹<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #27ae60;">
            <span>Total Collected:</span>
            <strong>₹<?php echo number_format($summary['total_received'] ?? 0, 2); ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between; color: #e74c3c;">
            <span>Total Pending:</span>
            <strong>₹<?php echo number_format($summary['total_pending'] ?? 0, 2); ?></strong>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" class="btn-add-item no-print">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<?php include 'views/components/footer.php'; ?>
