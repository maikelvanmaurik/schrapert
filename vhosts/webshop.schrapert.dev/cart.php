<?php
include_once 'bootstrap.php';
$items = $_SESSION['cart'];
if(count($items)): ?>
<h2>No items in your shopping cart!</h2>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?php print $item['name'] ?></td>
                <td><?php print $item['quantity']; ?></td>
                <td><?php print $item['price'] ?></td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif; ?>