<?php 
require_once __DIR__ . '/../config/auth.php';
requireLogin();
include __DIR__ . '/components/header.php'; 
?>

<form id="billForm" action="api/save_bill.php" method="POST" enctype="multipart/form-data">
    <?php echo getCSRFInput(); ?>
    <div class="bill-paper">
        <div class="bill-header">
            <h3>SHRI</h3>
            <h1>VIGNESH DECORATORS</h1>
            <p>Ph: 9448409817</p>
        </div>

        <div class="bill-info-row">
            <div class="bill-input-group" style="width: 45%;">
                <label>Bill No:</label>
                <input type="text" name="bill_number" placeholder="Enter number">
            </div>
            <div class="bill-input-group" style="width: 45%;">
                <label>Date:</label>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div class="bill-input-group">
            <label>Customer Name:</label>
            <input type="text" name="customer_name" required autofocus>
        </div>

        <div class="bill-info-row">
            <div class="bill-input-group" style="width: 45%;">
                <label>Phone Number:</label>
                <input type="tel" name="phone">
            </div>
            <div class="bill-input-group" style="width: 45%;">
                <label>Event Location:</label>
                <input type="text" name="address">
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
                <div class="item-row" style="grid-template-columns: 2fr 1fr 1fr 1.2fr auto;">
                    <div>
                        <select name="items[0][category]" style="font-size: 0.7rem; padding: 2px; margin-bottom: 2px;">
                            <option value="Decoration">Decoration</option>
                            <option value="Sound">Sound</option>
                            <option value="Lighting">Lighting</option>
                        </select>
                        <input type="text" name="items[0][name]" placeholder="Enter item" required>
                    </div>
                    <div><input type="number" step="0.01" name="items[0][quantity]" class="qty-input" placeholder="0"></div>
                    <div><input type="number" step="0.01" name="items[0][price]" class="price-input" placeholder="0"></div>
                    <div><input type="number" step="0.01" name="items[0][total]" class="row-total" placeholder="0.00"></div>
                    <div><button type="button" class="btn-remove" style="color:#e74c3c;">&times;</button></div>
                </div>
            </div>

            <button type="button" class="btn-add-item" id="addItemBtn" style="margin-top: 20px; border-style: solid; border-width: 1px;">
                <i class="fas fa-plus"></i> ADD ANOTHER ITEM
            </button>
        </div>

        <div class="bill-footer">
            <div class="total-row" style="padding: 15px 0; border-top: 2px solid #000;">
                <span style="font-size: 1.2rem;">GRAND TOTAL:</span>
                <span id="grandTotalDisplay" style="font-size: 1.5rem; color: #c0392b;">₹ 0.00</span>
                <input type="hidden" name="grand_total" id="grand_total_input" value="0">
            </div>

            <div class="bill-info-row" style="margin-top: 10px;">
                <div class="bill-input-group" style="width: 45%;">
                    <label>Amount Paid:</label>
                    <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" value="0">
                </div>
                <div class="bill-input-group" style="width: 45%;">
                    <label>Balance Due:</label>
                    <input type="text" id="balanceAmountDisplay" readonly value="0.00" style="color: #c0392b; font-weight: bold;">
                    <input type="hidden" name="balance_amount" id="balance_amount_input" value="0">
                </div>
            </div>

            <div class="bill-input-group">
                <label>Payment Status:</label>
                <select name="payment_status" id="paymentStatus">
                    <option value="Pending">Pending (No Payment)</option>
                    <option value="Partial">Partial Payment</option>
                    <option value="Paid">Fully Paid</option>
                </select>
            </div>

            <div class="bill-input-group">
                <label>Description / Event Photos:</label>
                <textarea name="description" rows="2" placeholder="Additional details..."></textarea>
                <input type="file" name="photos[]" multiple accept="image/*" style="margin-top: 10px; border: none;">
            </div>

            <div style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn-add-item" style="background: #27ae60; color: white; border: none; font-size: 1.2rem; padding: 15px; border-radius: 8px; box-shadow: 0 4px 10px rgba(39, 174, 96, 0.3);">
                    <i class="fas fa-save"></i> GENERATE BILL
                </button>
            </div>
        </div>
    </div>
</form>

<?php include 'views/components/footer.php'; ?>
