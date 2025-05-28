<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Winky+Rough:ital,wght@0,300..900;1,300..900&display=swap');
    body {
        font-family: outfit, serif;
        background: #f2f2f2;
        padding: 25px;
    }

    form {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        max-width: 900px;
        margin: auto;
    }

    h3 {
        margin-top: 30px;
        color: #333;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #333;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"],
    select,
    textarea {
        width: 100%;
        padding: 6px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 15px;
        background: #fafafa;
    }

    textarea {
        resize: vertical;
        height: 80px;
    }

    button[type="submit"],
    #add-product,
    .remove-product {
        background-color: lightseagreen;
        color: white;
        padding: 10px 18px;
        font-size: 14px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
        margin-right: 10px;
        transition: background 0.3s;
        align-items: center;

    }

    button:hover,
    #add-product:hover,
    .remove-product:hover {
        background-color: green;
    }

    .product-item {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 6px;
        background: #f9f9f9;
        margin-bottom: 20px;
        position: relative;
    }

    .remove-product {
        background-color: red;
        position: absolute;
        top: 6px;
        right: 0;
    }

    .remove-product:hover {
        background-color: red;
    }

    hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 20px 0;
    }
    .form-actions {
        text-align: right;
        margin-top: 20px;
    }

</style>
<?php echo view('page_heading'); ?>
<form method="post" action="<?= base_url('orders/new_order') ?>">
    <input type="hidden" name="created_at" value="<?= date('Y-m-d') ?>">
    <label>Order Code</label>
    <input type="text" name="order_code" value="<?= $order_code ?>" readonly><br>

    <label>Customer Name</label>
    <input type="text" name="customer_name" required><br>

    <label>Customer Phone</label>
    <input type="text" name="customer_phone" required><br>

    <label>Order Status</label>
    <select name="order_status">
        <option>select</option>
        <option>Pending</option>
        <option>Failed</option>
        <option>Completed</option>

    </select>
    <label>Order Date</label>
    <input type="date" name="order_date" required><br>
    <label>Payment Status</label>
    <select name="payment_status">
        <?php foreach ($payment_statuses as $key => $value): ?>
            <option value="<?= $key ?>"><?= $value ?></option>
        <?php endforeach; ?>
    </select><br>

    <hr>
    <h3>Products</h3>
    <div id="product-list">
    <div class="product-item">
        <select name="product_name[]" class="product-select" required>
            <option value="">-- Select Product --</option>
            <?php foreach ($products as $product): ?>
                <option
                        value="<?= $product['product_name'] ?>"
                        data-category="<?= $product['category'] ?>"
                        data-unit_price="<?= $product['unit_price' ]?>"
                        data-brand="<?= $product['brand' ]?>"
                        data-size="<?= $product['size' ]?>">
                    <?= $product['product_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="category[]" placeholder="Category" readonly>
        <input type="number" name="quantity[]" placeholder="Qty" min="1">
        <input type="number" name="unit_price[]" placeholder="Unit Price" readonly>
        <input type="text" name="brand[]" placeholder="Brand" readonly>
        <input type="text" name="size[]" placeholder="Size" readonly>
        <button type="button" class="remove-product">Remove</button>
        <hr>
    </div>
    </div>
    <button type="button" id="add-product">+ Add Product</button>

    <br><br>
    <div class="form-actions">
        <button type="submit" name="submit" value="1">Submit</button>
    </div>
</form>

<script>
    document.getElementById('add-product').addEventListener('click', function () {
        let productList = document.getElementById('product-list');
        let firstItem = productList.querySelector('.product-item');
        let newItem = firstItem.cloneNode(true);

        // Clear input values
        newItem.querySelectorAll('input, textarea').forEach(input => input.value = '');

        productList.appendChild(newItem);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-product')) {
            let items = document.querySelectorAll('.product-item');
            if (items.length > 1) {
                e.target.parentNode.remove();
            }
        }
    });

    //second
    $('#add-product').on('click', function () {
        let $firstItem = $('.product-item').first();
        let $newItem = $firstItem.clone();

        // Clear inputs
        $newItem.find('input').val('');
        $newItem.find('select').val('');

        $('#product-list').append($newItem);
    });

    // Remove product item
    $(document).on('click', '.remove-product', function () {
        if ($('.product-item').length > 1) {
            $(this).closest('.product-item').remove();
        }
    });

    // Auto-fill category, brand, price
    $(document).on('change', '.product-select', function () {
        let selected = $(this).find('option:selected');
        let container = $(this).closest('.product-item');

        container.find('input[name="category[]"]').val(selected.data('category'));
        container.find('input[name="unit_price[]"]').val(selected.data('unit_price'));
        container.find('input[name="brand[]"]').val(selected.data('brand'));
        container.find('input[name="size[]"]').val(selected.data('size'));
    });
</script>
