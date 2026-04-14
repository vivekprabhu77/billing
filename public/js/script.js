document.addEventListener('DOMContentLoaded', function() {
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');
    const grandTotalDisplay = document.getElementById('grandTotalDisplay');
    const grandTotalInput = document.getElementById('grand_total_input');
    const paidAmountInput = document.getElementById('paidAmountInput');
    const balanceAmountDisplay = document.getElementById('balanceAmountDisplay');
    const balanceAmountInput = document.getElementById('balance_amount_input');
    const paymentStatus = document.getElementById('paymentStatus');

    let rowCount = window.rowCount || 1;

    // Function to calculate totals
    function calculateTotals(e) {
        let grandTotal = 0;
        const rows = document.querySelectorAll('.item-row');

        rows.forEach(row => {
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const rowTotalInput = row.querySelector('.row-total');
            
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            
            // Logic: If the event was from qty or price input, update the row total.
            // If it was from the row total input itself, keep it (manual entry).
            if (e && (e.target === qtyInput || e.target === priceInput)) {
                if (qty > 0 || price > 0) {
                    const calculatedTotal = qty * price;
                    rowTotalInput.value = calculatedTotal > 0 ? calculatedTotal.toFixed(2) : rowTotalInput.value;
                }
            }
            
            const rowTotal = parseFloat(rowTotalInput.value) || 0;
            grandTotal += rowTotal;
        });

        grandTotalDisplay.textContent = '₹ ' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        grandTotalInput.value = grandTotal.toFixed(2);

        updateBalance();
    }

    function updateBalance() {
        const grandTotal = parseFloat(grandTotalInput.value) || 0;
        const paidAmount = parseFloat(paidAmountInput.value) || 0;
        const balance = grandTotal - paidAmount;

        balanceAmountDisplay.value = balance.toFixed(2);
        balanceAmountInput.value = balance.toFixed(2);

        // Auto update status based on balance
        if (grandTotal > 0) {
            if (paidAmount <= 0) {
                paymentStatus.value = 'Pending';
            } else if (paidAmount >= grandTotal) {
                paymentStatus.value = 'Paid';
            } else {
                paymentStatus.value = 'Partial';
            }
        }
    }

    // Add new item row
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'item-row';
            newRow.style.gridTemplateColumns = '2fr 1fr 1fr 1.2fr auto';
            newRow.innerHTML = `
                <div>
                    <select name="items[${rowCount}][category]" style="font-size: 0.7rem; padding: 2px; margin-bottom: 2px;">
                        <option value="Decoration">Decoration</option>
                        <option value="Sound">Sound</option>
                        <option value="Lighting">Lighting</option>
                    </select>
                    <input type="text" name="items[${rowCount}][name]" placeholder="Enter item" required>
                </div>
                <div><input type="number" step="0.01" name="items[${rowCount}][quantity]" class="qty-input" placeholder="0"></div>
                <div><input type="number" step="0.01" name="items[${rowCount}][price]" class="price-input" placeholder="0"></div>
                <div><input type="number" step="0.01" name="items[${rowCount}][total]" class="row-total" placeholder="0.00"></div>
                <div><button type="button" class="btn-remove" style="color:#e74c3c;">&times;</button></div>
            `;
            itemsContainer.appendChild(newRow);
            rowCount++;
        });
    }

    // Event delegation for inputs and remove button
    if (itemsContainer) {
        itemsContainer.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input') || 
                e.target.classList.contains('price-input') || 
                e.target.classList.contains('row-total')) {
                calculateTotals(e);
            }
        });

        itemsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove')) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            }
        });
    }

    if (paidAmountInput) {
        paidAmountInput.addEventListener('input', updateBalance);
    }
});
