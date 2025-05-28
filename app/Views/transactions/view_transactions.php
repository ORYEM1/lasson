<?php
echo view('page_heading');
?>
<table class="table table-striped table-hover">
    <tr><td>ID</td><td><?php echo $record['id'] ?></td></tr>
    <tr><td>Date Created</td><td><?php echo date('D, d M Y H:i:s T',strtotime($record['created_at'])) ?></td></tr>
    <tr><td>Product Name</td><td><?php echo $record['product_name'] ?></td></tr>
    <tr><td>Price</td><td><?php echo number_format($record['price'])?></td></tr>
    <tr><td>color</td><td><?php echo $record['color']?></td></tr>
    <tr><td>Category</td><td><?php echo $record['category']?></td></tr>
    <tr><td>Status</td><td><?php echo $record['status']?></td></tr>
    <tr><td>Brand</td><td><?php echo $record['brand']?></td></tr>
    <tr><td>Created By</td><td><?php echo $record['created_by'] ?></td></tr>
    <tr><td>Comment</td><td><?php echo $record['description'] ?></td></tr>
</table>
