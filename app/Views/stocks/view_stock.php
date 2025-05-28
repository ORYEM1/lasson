<?php echo view('page_heading'); ?>

<table class="table table-striped table-hover">
    <tr><td><strong>ID</strong></td><td><?= $record['stock_id'] ?? '' ?></td></tr>
    <tr><td><strong>Stock Code</strong></td><td><?= $record['stock_code'] ?? '' ?></td></tr>
    <tr><td><strong>Stock Type</strong></td><td><?= $record['stock_type'] ?? '' ?></td></tr>
    <tr><td><strong>Supplier Name</strong></td><td><?= $record['supplier_name'] ?? '' ?></td></tr>
    <tr><td><strong>Receiver Name</strong></td><td><?= $record['receiver_name'] ?? '' ?></td></tr>
    <tr><td><strong>Stock Date</strong></td><td><?= $record['stock_date'] ?? '' ?></td></tr>
    <tr><td><strong>Payment Status</strong></td><td><?= $record['payment_status'] ?? '' ?></td></tr>
    <tr><td><strong>Ordered On</strong></td><td><?= $record['created_at'] ?? '' ?></td></tr>
</table>

<?php if (!empty($products) && is_array($products)) : ?>
    <h4 class="mt-4">Products in this Stock</h4>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Product ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total Price</th>
            <th>Size</th>
            <th>Color</th>
            <th>Brand</th>
            <td>Status</td>
            <th>Description</th>

        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $i => $product): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $product['product_id'] ?? '' ?></td>
                <td><?= $product['product_name'] ?? '' ?></td>
                <td><?= $product['category'] ?? '' ?></td>
                <td><?= $product['quantity'] ?? '' ?></td>
                <td><?= $product['unit_price'] ?? '' ?></td>
                <td><?= $product['total_price'] ?? '' ?></td>
                <td><?= $product['size'] ?? '' ?></td>
                <td><?= $product['brand'] ?? '' ?></td>
                <td><?= $product['status'] ?? '' ?></td>
                <td><?= $product['description'] ?? '' ?></td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>No products found for this stock.</p>
<?php endif; ?>

<!-- Optional Category Dropdown -->
<div class="mt-4">
    <label><strong>Choose Category:</strong></label>
    <select name="category" class="form-control">
        <?php foreach (get_category() as $group => $items): ?>
            <optgroup label="<?= esc($group) ?>">
                <?php foreach ($items as $item): ?>
                    <option value="<?= esc($item) ?>"><?= esc($item) ?></option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>
</div>
