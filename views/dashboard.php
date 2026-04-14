<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$conn = getDBConnection();

// Summary stats
$total_earnings = $conn->query("SELECT SUM(paid_amount) as total FROM payments")->fetch_assoc()['total'] ?? 0;
$pending_amount = $conn->query("SELECT SUM(balance_amount) as total FROM payments WHERE status != 'Paid'")->fetch_assoc()['total'] ?? 0;
$today_income = $conn->query("SELECT SUM(paid_amount) as total FROM payments p JOIN bills b ON p.bill_id = b.id WHERE DATE(b.date) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$total_bills_count = $conn->query("SELECT COUNT(*) as total FROM bills")->fetch_assoc()['total'] ?? 0;

$recent_bills_query = $conn->query("SELECT b.*, p.status, p.balance_amount FROM bills b JOIN payments p ON b.id = p.bill_id ORDER BY b.date DESC, b.created_at DESC LIMIT 10");

include __DIR__ . '/components/header.php';
?>

<style>
    .summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 25px; }
    .sum-card { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid var(--accent-color); }
    .sum-card h4 { font-size: 0.75rem; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .sum-card .value { font-size: 1.2rem; font-weight: bold; color: #2c3e50; }
    
    .status-badge { padding: 4px 10px; border-radius: 12px; color: white; font-size: 0.7rem; font-weight: bold; }
    .status-paid { background: #27ae60; }
    .status-partial { background: #f1c40f; color: #2c3e50; }
    .status-pending { background: #e74c3c; }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Dashboard</h2>
    <span style="font-size: 0.8rem; color: #7f8c8d;"><?php echo date('d M Y'); ?></span>
</div>

<div class="summary-grid">
    <div class="sum-card" style="border-left-color: #27ae60;">
        <h4>Earnings</h4>
        <div class="value">₹<?php echo number_format($total_earnings, 0); ?></div>
    </div>
    <div class="sum-card" style="border-left-color: #e74c3c;">
        <h4>Pending</h4>
        <div class="value">₹<?php echo number_format($pending_amount, 0); ?></div>
    </div>
    <div class="sum-card" style="border-left-color: #3498db;">
        <h4>Today</h4>
        <div class="value">₹<?php echo number_format($today_income, 0); ?></div>
    </div>
    <div class="sum-card" style="border-left-color: #f39c12;">
        <h4>Total Billed</h4>
        <div class="value"><?php echo $total_bills_count; ?></div>
    </div>
</div>

<div style="margin-top: 30px;">
    <div class="bill-input-group" style="position: relative;">
        <i class="fas fa-search" style="position: absolute; right: 10px; top: 12px; color: #7f8c8d;"></i>
        <input type="text" id="dashboardSearch" placeholder="Search Name, Phone, Date or No..." onkeyup="searchBills()" style="padding-right: 35px; border-radius: 20px; border: 1px solid #ddd; padding: 10px 15px; width: 100%;">
    </div>
</div>

<div style="margin-top: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3>Recent Bills</h3>
        <a href="dashboard.php?page=bills" style="font-size: 0.85rem; color: #3498db; text-decoration: none;">View All <i class="fas fa-chevron-right"></i></a>
    </div>

    <div id="recentBillsList">
    <?php while($row = $recent_bills_query->fetch_assoc()): ?>
        <a href="dashboard.php?page=view_bill&id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
            <div class="bill-paper" style="min-height: auto; padding: 15px; margin-bottom: 12px; border-radius: 8px; border-left: 6px solid <?php 
                echo ($row['status'] == 'Paid' ? '#27ae60' : ($row['status'] == 'Partial' ? '#f1c40f' : '#e74c3c')); 
            ?>;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-weight: bold; font-size: 1.05rem;"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                        <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 3px;">
                            <?php echo $row['bill_number'] ? 'Bill #'.$row['bill_number'] : 'Ref #'.$row['id']; ?> | 
                            <?php echo date('d M Y', strtotime($row['date'])); ?>
                        </div>
                        <?php if($row['phone']): ?>
                            <div style="font-size: 0.8rem; color: #7f8c8d;"><i class="fas fa-phone-alt" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo strtoupper($row['status']); ?></span>
                        <div style="font-weight: bold; margin-top: 5px; color: #2c3e50;">₹<?php echo number_format($row['grand_total'], 0); ?></div>
                    </div>
                </div>
            </div>
        </a>
    <?php endwhile; ?>
    </div>
</div>

<script>
function searchBills() {
    let input = document.getElementById('dashboardSearch').value.toLowerCase();
    let cards = document.querySelectorAll('#recentBillsList > a');
    
    cards.forEach(card => {
        let text = card.textContent.toLowerCase();
        card.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

<?php include 'views/components/footer.php'; ?>
