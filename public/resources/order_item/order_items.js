$(document).on('click', '#add-item-btn', function () {
    let row = `
        <div class="form-row">
            <div>
                <label>Product ID</label>
                <input type="text" name="product_id[]" required>
            </div>
            <div>
                <label>Product Name</label>
                <input type="text" name="product_name[]" required>
            </div>
            <div>
                <label>Quantity</label>
                <input type="number" name="quantity[]" required>
            </div>
            <div>
                <label>Unit Price</label>
                <input type="number" name="unit_price[]" step="0.01" required>
            </div>
            <div>
                <label>&nbsp;</label>
                <button type="button" class="remove-btn">Remove</button>
            </div>
        </div>`;
    $('#order-items-container').append(row);
});

$(document).on('click', '.remove-btn', function () {
    $(this).closest('.form-row').remove();
});
