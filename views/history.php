<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$conn = getDBConnection();

$bills_query = $conn->query("
    SELECT b.*, p.status, p.paid_amount, p.balance_amount 
    FROM bills b 
    JOIN payments p ON b.id = p.bill_id 
    ORDER BY b.date DESC, b.created_at DESC
");

include __DIR__ . '/components/header.php';
?>

<style>
    .status-badge { padding: 4px 10px; border-radius: 12px; color: white; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; }
    .status-paid { background: #27ae60; }
    .status-partial { background: #f1c40f; color: #2c3e50; }
    .status-pending { background: #e74c3c; }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Bill History</h2>
    <a href="dashboard.php?page=create_bill" style="background: var(--accent-color); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-size: 0.9rem; font-weight: bold;">+ New Bill</a>
</div>

<div class="bill-input-group" style="position: relative; margin-bottom: 25px;">
    <i class="fas fa-search" style="position: absolute; right: 15px; top: 12px; color: #7f8c8d;"></i>
    <input type="text" id="historySearch" placeholder="Search Name, Phone, Date, Bill No..." onkeyup="searchHistory()" style="width: 100%; padding: 12px 15px; border-radius: 25px; border: 1px solid #ddd; outline: none; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
</div>

<div id="historyList">
    <?php if ($bills_query->num_rows > 0): ?>
        <?php while($row = $bills_query->fetch_assoc()): ?>
            <a href="dashboard.php?page=view_bill&id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                <div class="bill-paper" style="min-height: auto; padding: 15px; margin-bottom: 15px; border-radius: 8px; border-left: 6px solid <?php 
                    echo ($row['status'] == 'Paid' ? '#27ae60' : ($row['status'] == 'Partial' ? '#f1c40f' : '#e74c3c')); 
                ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <strong style="font-size: 1.1rem; color: #2c3e50;"><?php echo htmlspecialchars($row['customer_name']); ?></strong>
                            <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 4px;">
                                <?php echo $row['bill_number'] ? 'Bill: '.$row['bill_number'] : 'Ref: '.$row['id']; ?> | 
                                <i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($row['date'])); ?>
                            </div>
                            <?php if($row['phone']): ?>
                                <div style="font-size: 0.8rem; color: #7f8c8d; margin-top: 2px;"><i class="fas fa-phone-alt" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($row['phone']); ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px; border-top: 1px dashed #eee;">
                        <div>
                            <span style="font-size: 0.8rem; color: #7f8c8d;">TOTAL:</span> 
                            <span style="font-weight: bold; color: #2c3e50;">₹<?php echo number_format($row['grand_total'], 2); ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.8rem; color: #7f8c8d;">DUE:</span> 
                            <strong style="color: #e74c3c;">₹<?php echo number_format($row['balance_amount'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; color: #bdc3c7;">
            <i class="fas fa-file-invoice" style="font-size: 4rem; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No bills found in history.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function searchHistory() {
    let input = document.getElementById('historySearch').value.toLowerCase();
    let cards = document.querySelectorAll('#historyList > a');
    
    cards.forEach(card => {
        let text = card.textContent.toLowerCase();
        card.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

<?php include 'views/components/footer.php'; ?>
