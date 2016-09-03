<?php include 'functions.php' ?>
<?php ob_start(); ?>
<?php $products = json_decode(file_get_contents(__DIR__.'/etc/products.json'), true); ?>
<form method="post" action="cart.php">
    <input type="hidden" name="action" value="add" />
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($products as $product): ?>
            <tr>
                <td><?php echo $product['name'] ?></td>
                <td><?php printf("%.2f", $product['price']) ?></td>
                <td><button type="submit" value="<?php echo $product['id'] ?>">Add to cart</button></td>

            </tr>
        <?php endforeach; ?>
        </tbody>
</form>
<?php print do_layout(ob_get_clean()); ?>