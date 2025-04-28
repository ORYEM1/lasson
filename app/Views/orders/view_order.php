<?php
echo view('page_heading');
?>
<table class="table table-striped table-hover">
    <tr><td>ID</td><td><?php echo $record['order_id'] ?></td></tr>
    <tr><td>Order Number</td><td><?php echo $record['order_number'] ?></td></tr>
    <tr><td>Customer Name</td><td><?php echo $record['customer_name'] ?></td></tr>
    <tr><td>Customer Email</td><td><?php echo $record['customer_email']?></td></tr>
    <tr><td>Customer Phone</td><td><?php echo $record['phone_number']?></td></tr>
    <tr><td>Product</td><td><?php echo $record['product']?></td></tr>
    <tr><td>Quantity</td><td><?php echo $record['quantity']?></td></tr>
    <tr><td>Total Amount</td><td><?php echo $record['total_amount']?></td></tr>
    <tr><td>Order Status</td><td><?php echo $record['order_status']?></td></tr>
    <tr><td>Delivery Address</td><td><?php echo $record['delivery_address']?></td></tr>
    <tr><td>Delivery Date</td><td><?php echo $record['delivery_date'] ?></td></tr>
    <tr><td>Payment Method</td><td><?php echo $record['payment_method']?></td></tr>
    <tr><td>Payment Status</td><td><?php echo $record['payment_status']?></td></tr>

    <tr><td>Ordered On</td><td><?php echo $record['ordered_on'] ?></td></tr>

</table>












