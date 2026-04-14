<?php 
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/utils.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$conn = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid ID.");

$stmt1 = $conn->prepare("SELECT * FROM bills WHERE id = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();
$bill = $stmt1->get_result()->fetch_assoc();

if (!$bill) {
    die("Bill not found.");
}

$stmt2 = $conn->prepare("SELECT * FROM bill_items WHERE bill_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$items_query = $stmt2->get_result();

$stmt3 = $conn->prepare("SELECT * FROM payments WHERE bill_id = ?");
$stmt3->bind_param("i", $id);
$stmt3->execute();
$payment = $stmt3->get_result()->fetch_assoc();

$stmt4 = $conn->prepare("SELECT * FROM bill_media WHERE bill_id = ?");
$stmt4->bind_param("i", $id);
$stmt4->execute();
$media_query = $stmt4->get_result();

include __DIR__ . '/components/header.php'; 
?>

<style>
    .status-badge { padding: 4px 12px; border-radius: 20px; color: white; font-weight: bold; font-size: 0.8rem; }
    .status-paid { background-color: #27ae60; }
    .status-partial { background-color: #f1c40f; color: #2c3e50; }
    .status-pending { background-color: #e74c3c; }
    
    .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { background: white; margin: 20% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 400px; }
</style>

<div class="no-print" style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
    <button onclick="window.print()" class="btn-add-item" style="background: #2c3e50; color: white; border: none;">
        <i class="fas fa-print"></i> PRINT
    </button>
    <a href="dashboard.php?page=edit_bill&id=<?php echo $id; ?>" class="btn-add-item" style="background: #3498db; color: white; border: none; text-decoration: none; text-align: center;">
        <i class="fas fa-edit"></i> EDIT
    </a>
    <button onclick="document.getElementById('paymentModal').style.display='block'" class="btn-add-item" style="background: #f39c12; color: white; border: none;">
        <i class="fas fa-plus"></i> ADD PAYMENT
    </button>
    <form action="api/update_payment.php" method="POST" style="margin:0;">
        <?php echo getCSRFInput(); ?>
        <input type="hidden" name="bill_id" value="<?php echo $id; ?>">
        <input type="hidden" name="amount_to_add" value="<?php echo $payment['balance_amount']; ?>">
        <button type="submit" class="btn-add-item" style="background: #27ae60; color: white; border: none; width: 100%;">
            <i class="fas fa-check-double"></i> MARK PAID
        </button>
    </form>
</div>

<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h3>Add Payment</h3>
        <form action="api/update_payment.php" method="POST">
            <?php echo getCSRFInput(); ?>
            <input type="hidden" name="bill_id" value="<?php echo $id; ?>">
            <div class="bill-input-group">
                <label>Amount to Add (₹)</label>
                <input type="number" step="0.01" name="amount_to_add" required autofocus>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-add-item" style="background: var(--accent-color); color: white; border: none; flex: 1;">SAVE</button>
                <button type="button" onclick="document.getElementById('paymentModal').style.display='none'" class="btn-add-item" style="background: #95a5a6; color: white; border: none; flex: 1;">CLOSE</button>
            </div>
        </form>
    </div>
</div>

<div class="bill-paper" id="printableBill">
    <div class="bill-header">
        <p style="margin: 0; font-size: 0.8rem; font-weight: bold;">CASH/CREDIT</p>
        <p style="text-align: right; margin: 0; font-size: 1rem; font-weight: bold;">Mob: 9448409817</p>
        <h3 style="margin-top: -10px;">SHRI</h3>
        <h1 style="font-size: 2.2rem; margin: 5px 0;">VIGNESH DECORATORS</h1>
        <p style="font-size: 0.85rem;">'Shri Vignesh Krupa', Uppunda, Tq: Byndoor, Udupi Dist.</p>
    </div>

    <div class="bill-info-row" style="margin-top: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <div style="width: 65%;">
            <strong>Name:</strong> <?php echo htmlspecialchars($bill['customer_name']); ?><br>
            <strong>Phone:</strong> <?php echo htmlspecialchars($bill['phone']); ?><br>
            <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($bill['address'])); ?>
        </div>
        <div style="width: 30%; text-align: right;">
            <strong>No:</strong> <span style="font-size: 1.4rem; font-weight: bold;"><?php echo $bill['bill_number'] ? htmlspecialchars($bill['bill_number']) : 'Ref-'.$bill['id']; ?></span><br>
            <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($bill['date'])); ?>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #000; text-align: left;">
                    <th style="padding: 10px 5px; width: 10%;">SL.No.</th>
                    <th style="padding: 10px 5px; width: 65%;">Particulars</th>
                    <th style="padding: 10px 5px; width: 25%; text-align: right;">Amount(₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                while($item = $items_query->fetch_assoc()): 
                ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 15px 5px; vertical-align: top;"><?php echo $i++; ?>.</td>
                    <td style="padding: 15px 5px;">
                        <span style="font-size: 1.1rem;"><?php echo htmlspecialchars($item['item_name']); ?></span>
                        <?php if ($item['quantity'] > 0 && $item['price'] > 0): ?>
                            <div style="font-size: 0.85rem; color: #666; margin-top: 3px;">
                                <?php echo $item['quantity']; ?> x ₹<?php echo number_format($item['price'], 2); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 5px; text-align: right; vertical-align: top;">
                        <strong><?php echo number_format($item['total'], 2); ?></strong>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php for($j=0; $j < max(0, 5 - $items_query->num_rows); $j++): ?>
                <tr><td colspan="3" style="height: 40px;"></td></tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #000; font-weight: bold;">
                    <td colspan="2" style="padding: 15px 5px; text-align: right; font-size: 1.3rem;">TOTAL</td>
                    <td style="padding: 15px 5px; text-align: right; font-size: 1.3rem;">
                        ₹<?php echo number_format($bill['grand_total'], 2); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">
        <p style="text-transform: capitalize; font-style: italic;">Rupees in words: <strong><?php echo getIndianCurrencyInWords($bill['grand_total']); ?></strong></p>
    </div>

    <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="width: 60%; background: #fdfefe; border: 1px solid #eee; padding: 15px; border-radius: 4px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Total Amount:</span>
                <strong>₹<?php echo number_format($payment['total_amount'], 2); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px; color: #27ae60;">
                <span>Paid Amount:</span>
                <strong>₹<?php echo number_format($payment['paid_amount'], 2); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #c0392b;">
                <span>Balance Due:</span>
                <strong>₹<?php echo number_format($payment['balance_amount'], 2); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Status:</span>
                <span class="status-badge status-<?php echo strtolower($payment['status']); ?>"><?php echo strtoupper($payment['status']); ?></span>
            </div>
        </div>
        <div style="text-align: center; width: 30%; font-weight: bold;">
            <div style="padding-top: 50px; border-top: 1px solid #000;">Signature</div>
        </div>
    </div>

    <?php if ($bill['description']): ?>
    <div class="no-print" style="margin-top: 40px; border-top: 1px dashed #ccc; padding-top: 10px;">
        <h4>Notes:</h4>
        <p style="color: #555;"><?php echo nl2br(htmlspecialchars($bill['description'])); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($media_query->num_rows > 0): ?>
    <div class="no-print" style="margin-top: 20px;">
        <h4>Photos:</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
            <?php while($m = $media_query->fetch_assoc()): ?>
                <img src="<?php echo $m['file_path']; ?>" style="width: 100%; border-radius: 4px; border: 1px solid #ddd; aspect-ratio: 1; object-fit: cover;">
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'views/components/footer.php'; ?>
