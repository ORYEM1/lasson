<?php
echo view('page_heading');

?>
echo view('page_heading');
<h5>Product List</h5>
<table class="table table-bordered">
    <thead>
    <tr>
        <th>#</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Total Price</th>
        <th>Brand</th>
        <th>Size</th>
        <td>Status</td>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($products)) : ?>
        <?php foreach ($products as $i => $product) : ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $product['product_name'] ?></td>
                <td><?= $product['category'] ?></td>
                <td><?= $product['quantity'] ?></td>
                <td><?= $product['unit_price'] ?></td>
                <td><?= $product['total_price'] ?></td>
                <td><?= $product['brand'] ?></td>
                <td><?= $product['size'] ?></td>
                <td><?= $product['status'] ?></td>
                <td><?= $product['description'] ?></td>

            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr><td colspan="9">No products found for this stock.</td></tr>
    <?php endif; ?>
    </tbody>
</table>


