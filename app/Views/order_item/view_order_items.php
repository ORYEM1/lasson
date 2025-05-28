
<!--
<?php
echo view('page_heading');
?>
<table class="table table-striped table-hover">
    <tr><td>ID</td><td><?php echo $record['id'] ?></td></tr>
    <tr><td>Product Name</td><td><?php echo $record['product_name'] ?></td></tr>
    <tr><td>Category</td><td><?php echo $record['category']?></td></tr>
    <tr><td>Quantity</td><td><?php echo number_format($record['quantity'])?></td></tr>
    <tr><td>Unit Price</td><td><?php echo $record['unit_price']?></td></tr>
    <tr><td>Total Price</td><td><?php echo $record['total_price']?></td></tr>

</table>

<tfoot>
<tr>
    <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
    <td><strong><?= number_format($total_order_price, 2) ?> UGX</strong></td>
</tr>
</tfoot>
<script>
    function calculateTotalPrice() {
        let quantity = parseFloat(document.getElementById('quantity').value) || 0;
        let price = parseFloat(document.getElementById('unit_price').value) || 0;
        let total = quantity * price;
        document.getElementById('total_price').value = total.toFixed(2);
    }
</script>-->

<?php echo view('page_heading'); ?>

<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Unit Price (UGX)</th>
        <th>Total Price (UGX)</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $grand_total = 0;
    if (!empty($order_items) && is_array($order_items)):
        foreach ($order_items as $record):
            $grand_total += $record['total_price'];
            ?>
            <tr>
                <td><?php echo $record['id']; ?></td>
                <td><?php echo $record['product_name']; ?></td>
                <td><?php echo $record['category']; ?></td>
                <td><?php echo number_format($record['quantity']); ?></td>
                <td><?php echo number_format($record['unit_price'], 2); ?></td>
                <td><?php echo number_format($record['total_price'], 2); ?></td>
            </tr>
        <?php
        endforeach;
    endif;
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3"><strong>Total</strong></td>
        <td><strong><?= $total_quantity_sum ?></strong></td>
        <td></td>
        <td><strong><?= number_format($total_price_sum, 2) ?></strong></td>
        <td></td>
        <td></td>
    </tr>
    </tfoot>
</table>




