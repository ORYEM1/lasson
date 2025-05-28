<!DOCTYPE html>
<html>
<head>
    <title>Order Items</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.min.css') ?>">
</head>
<body>

<div class="container mt-4">
    <h2>Order Items</h2>



    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total Amount</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data as $record): ?>
            <tr>
                <td><?= $record['id'] ?></td>
                <td><?= $record['product_name'] ?></td>
                <td><?= $record['category'] ?></td>
                <td><?= $record['quantity'] ?></td>
                <td><?= $record['price'] ?></td>
                <td><?= $record['total_price'] ?></td>
                <td>
                    <a href="<?= base_url('order_items/view_order_items/' . $record['id']) ?>" class="btn btn-info btn-sm">View</a>
                    <a href="<?= base_url('order_items/edit_order_item/' . $record['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="<?= base_url('/order_items/add_product') ?>" class="btn btn-primary mb-3">Add New Product</a>
    <a href="<?= base_url('/orders/new_order') ?>" class="btn btn-success mb-3">Proceed to Payment</a>
</div>

</body>
</html>
