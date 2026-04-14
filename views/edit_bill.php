<?php 
require_once 'config/database.php';
require_once 'config/auth.php';
requireLogin();

$conn = getDBConnection();

$id = $_GET['id'];
$bill_query = $conn->query("SELECT * FROM bills WHERE id = $id");
$bill = $bill_query->fetch_assoc();

if (!$bill) die("Bill not found.");

$items_query = $conn->query("SELECT * FROM bill_items WHERE bill_id = $id");
$payment_query = $conn->query("SELECT * FROM payments WHERE bill_id = $id");
$payment = $payment_query->fetch_assoc();

include 'views/components/header.php'; 
?>

<form id="billForm" action="api/update_bill.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="bill_id" value="<?php echo $id; ?>">
    
    <div class="bill-paper">
        <div class="bill-header">
            <h3>EDIT BILL</h3>
            <h1>VIGNESH DECORATORS</h1>
        </div>

        <div class="bill-info-row">
            <div class="bill-input-group" style="width: 45%;">
                <label>Bill No:</label>
                <input type="text" name="bill_number" value="<?php echo htmlspecialchars($bill['bill_number']); ?>">
            </div>
            <div class="bill-input-group" style="width: 45%;">
                <label>Date:</label>
                <input type="date" name="date" value="<?php echo $bill['date']; ?>" required>
            </div>
        </div>

        <div class="bill-input-group">
            <label>Customer Name:</label>
            <input type="text" name="customer_name" value="<?php echo htmlspecialchars($bill['customer_name']); ?>" required>
        </div>

        <div class="bill-info-row">
            <div class="bill-input-group" style="width: 45%;">
                <label>Phone Number:</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($bill['phone']); ?>">
            </div>
            <div class="bill-input-group" style="width: 45%;">
                <label>Event Location:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($bill['address']); ?>">
            </div>
        </div>

        <div class="item-entry-container">
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr auto; font-weight: bold; margin-bottom: 5px; font-size: 0.8rem; border-bottom: 1px solid #000; padding-bottom: 5px;">
                <div>Item Name</div>
                <div>Qty</div>
                <div>Price</div>
                <div>Total (₹)</div>
                <div></div>
            </div>
            <div id="itemsContainer">
                <?php 
                $rowCount = 0;
                while($item = $items_query->fetch_assoc()): 
                ?>
                <div class="item-row" style="grid-template-columns: 2fr 1fr 1fr 1.2fr auto;">
                    <div>
                        <select name="items[<?php echo $rowCount; ?>][category]" style="font-size: 0.7rem; padding: 2px; margin-bottom: 2px;">
                            <option value="Decoration" <?php echo $item['category'] == 'Decoration' ? 'selected' : ''; ?>>Decoration</option>
                            <option value="Sound" <?php echo $item['category'] == 'Sound' ? 'selected' : ''; ?>>Sound</option>
                            <option value="Lighting" <?php echo $item['category'] == 'Lighting' ? 'selected' : ''; ?>>Lighting</option>
                        </select>
                        <input type="text" name="items[<?php echo $rowCount; ?>][name]" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                    </div>
                    <div><input type="number" step="0.01" name="items[<?php echo $rowCount; ?>][quantity]" class="qty-input" value="<?php echo $item['quantity']; ?>"></div>
                    <div><input type="number" step="0.01" name="items[<?php echo $rowCount; ?>][price]" class="price-input" value="<?php echo $item['price']; ?>"></div>
                    <div><input type="number" step="0.01" name="items[<?php echo $rowCount; ?>][total]" class="row-total" value="<?php echo $item['total']; ?>"></div>
                    <div><button type="button" class="btn-remove" style="color: #e74c3c;">&times;</button></div>
                </div>
                <?php 
                $rowCount++;
                endwhile; 
                ?>
            </div>

            <button type="button" class="btn-add-item" id="addItemBtn" style="margin-top: 20px; border-style: solid; border-width: 1px;">
                <i class="fas fa-plus"></i> ADD ANOTHER ITEM
            </button>
        </div>

        <div class="bill-footer">
            <div class="total-row" style="padding: 15px 0; border-top: 2px solid #000;">
                <span style="font-size: 1.2rem;">GRAND TOTAL:</span>
                <span id="grandTotalDisplay" style="font-size: 1.5rem; color: #c0392b;">₹ <?php echo number_format($bill['grand_total'], 2); ?></span>
                <input type="hidden" name="grand_total" id="grand_total_input" value="<?php echo $bill['grand_total']; ?>">
            </div>

            <div class="bill-info-row" style="margin-top: 10px;">
                <div class="bill-input-group" style="width: 45%;">
                    <label>Amount Paid:</label>
                    <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" value="<?php echo $payment['paid_amount']; ?>">
                </div>
                <div class="bill-input-group" style="width: 45%;">
                    <label>Balance Due:</label>
                    <input type="text" id="balanceAmountDisplay" readonly value="<?php echo $payment['balance_amount']; ?>" style="color: #c0392b; font-weight: bold;">
                    <input type="hidden" name="balance_amount" id="balance_amount_input" value="<?php echo $payment['balance_amount']; ?>">
                </div>
            </div>

            <div class="bill-input-group">
                <label>Payment Status:</label>
                <select name="payment_status" id="paymentStatus">
                    <option value="Pending" <?php echo $payment['status'] == 'Pending' ? 'selected' : ''; ?>>Pending (No Payment)</option>
                    <option value="Partial" <?php echo $payment['status'] == 'Partial' ? 'selected' : ''; ?>>Partial Payment</option>
                    <option value="Paid" <?php echo $payment['status'] == 'Paid' ? 'selected' : ''; ?>>Fully Paid</option>
                </select>
            </div>

            <div class="bill-input-group">
                <label>Notes:</label>
                <textarea name="description" rows="2"><?php echo htmlspecialchars($bill['description']); ?></textarea>
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn-add-item" style="background: #2980b9; color: white; border: none; font-size: 1.2rem; padding: 15px; border-radius: 8px;">
                    <i class="fas fa-save"></i> UPDATE BILL
                </button>
            </div>
        </div>
    </div>
</form>

<script>
window.rowCount = <?php echo $rowCount; ?>;
</script>

<?php include 'views/components/footer.php'; ?>
