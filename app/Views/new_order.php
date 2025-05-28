<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details - <?= $record['order_number'] ?? '' ?></title>

</head>
<body>

<div class="container my-5">
    <h2 class="mb-4">Order Details</h2>

    <table class="table table-striped table-hover">
        <tr><td><strong>ID</strong></td><td><?= $record['order_id'] ?></td></tr>
        <tr><td><strong>Order Number</strong></td><td><?= $record['order_number'] ?></td></tr>
        <tr><td><strong>Customer Name</strong></td><td><?= $record['customer_name'] ?></td></tr>
        <tr><td><strong>Customer Email</strong></td><td><?= $record['customer_email'] ?></td></tr>
        <tr><td><strong>Customer Phone</strong></td><td><?= $record['phone_number'] ?></td></tr>
        <tr><td><strong>Total Amount</strong></td><td><?= $record['total_amount'] ?></td></tr>
        <tr><td><strong>Order Status</strong></td><td><?= $record['order_status'] ?></td></tr>
        <tr><td><strong>Delivery Address</strong></td><td><?= $record['delivery_address'] ?></td></tr>
        <tr><td><strong>Delivery Date</strong></td><td><?= $record['delivery_date'] ?></td></tr>
        <tr><td><strong>Payment Method</strong></td><td><?= $record['payment_method'] ?></td></tr>
        <tr><td><strong>Payment Status</strong></td><td><?= $record['payment_status'] ?></td></tr>
        <tr><td><strong>Ordered On</strong></td><td><?= $record['ordered_on'] ?></td></tr>
    </table>

    <h4 class="mt-5">Ordered Products</h4>
    <table class="table table-bordered">
        <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total Price</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($items)): $i = 1; foreach ($items as $item): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= $item['product_name'] ?></td>
                <td><?= number_format($item['unit_price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['total_price'], 2) ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5">No products found for this order.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
