<?php
echo view('page_heading');
?>
<table class="table table-striped table-hover">
    <tr><td>ID</td><td><?php echo $record['stock_id'] ?></td></tr>
    <tr><td>Product ID</td><td><?php echo $record['product_id']?></td></tr>
    <tr><td>Stock Code</td><td><?php echo $record['stock_code'] ?></td></tr>
    <tr><td>Stock Type</td><td><?php echo $record['stock_type'] ?></td></tr>
    <tr><td>Quantity</td><td><?php echo $record['quantity']?></td></tr>
    <tr><td>Unit Price</td><td><?php echo $record['unit_price']?></td></tr>
    <tr><td>Total Price</td><td><?php echo $record['total_price']?></td></tr>
    <tr><td>Supplier Name</td><td><?php echo $record['supplier_name']?></td></tr>
    <tr><td>Receiver Name</td><td><?php echo $record['receiver_name']?></td></tr>
    <tr><td>Stock Date</td><td><?php echo $record['stock_date']?></td></tr>
    <tr><td>Payment Status</td><td><?php echo $record['payment_status']?></td></tr>
    <tr><td>Status</td><td><?php echo $record['status'] ?></td></tr>
    <tr><td>Ordered On</td><td><?php echo $record['created_at'] ?></td></tr>

</table>