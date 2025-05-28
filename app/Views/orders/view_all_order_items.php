
<style>
    @media print {
        button {
            display: none;
        }
        body {
            margin: 0;
            font-size: 12pt;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        img{
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }
    }
</style>
<h2 style="align-content: center;justify-content: center;color: lightseagreen;text-align: center"><img style="width: 50px;height: 50px;border-radius: 50%;border: 1px solid black" src="/resources/images/logo.png">LASSON HARDWARE</h2>
<h5 style="justify-content: center;text-align: center">www.lassonhardwareug.com</h5>
<h5 style="justify-content: center;text-align: center">Location:Kira-Kasangati Road</h5>
<h5 style="justify-content: center;text-align: center;color: red">Tel:+256780227604</h5>
<p>*****************************************************************************</p>
<br>
<h3 style="align-items: center"><?= esc($page_heading) ?></h3>
<p><strong>Serviced By:</strong> <?= esc($username) ?></p>
<p><strong>Time:</strong> <?= esc($current_time) ?></p>

<p>Customer Name:</p>


<table border="1" cellpadding="10" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <!--<th>Category</th>-->
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Total Price</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($record)): ?>
        <?php foreach ($record as $item): ?>
            <tr>
                <td><?= esc($item['id']) ?></td>
                <td><?= esc($item['product_name']) ?></td>
                <!-- <td><?= esc($item['category']) ?></td>-->
                <td><?= esc($item['quantity']) ?></td>
                <td><?= number_format($item['unit_price'], 0) ?></td>
                <td><?= number_format($item['total_price'], 0) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6">No order items found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>


<br>

<h4>Summary</h4>


<p><strong>Total Quantity:</strong> <?= esc($total_quantity_sum) ?></p>
<p><strong>Total Cost:</strong> UGX <?= number_format($total_price_sum, 0) ?></p>
<button onclick="window.print()" style="margin-bottom: 20px; padding: 8px 16px;background-color: lightseagreen;border: none;border-radius: 5px;justify-content: center ;font-size: 14px;">
    Print
</button>
<h6 style="font-style: italic;text-align: center">Goods once sold are not returnable</h6>


