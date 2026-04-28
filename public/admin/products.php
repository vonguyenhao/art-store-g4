<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$productsRepository = app('products');
$view = app('view');
$auth->require();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    try {
        $productNo = (int) ($_POST['product_no'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);

        if ($description === '' || $category === '' || $price <= 0) {
            throw new RuntimeException('Description, category, and a positive price are required.');
        }

        $productsRepository->save([
            'product_no' => $productNo,
            'description' => $description,
            'category' => $category,
            'price' => $price,
            'colour' => trim($_POST['colour'] ?? ''),
            'size' => trim($_POST['size'] ?? ''),
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
        ]);
        $session->flash($productNo > 0 ? 'Product updated.' : 'Product added.');

        redirect('/admin/products.php');
    } catch (Throwable $error) {
        $productError = $error->getMessage();
    }
}

$view->header('Products');

try {
    $products = $productsRepository->all();
} catch (Throwable $error) {
    echo '<p class="error">' . e(dbErrorMessage($error)) . '</p>';
    $view->footer();
    exit;
}
?>

<h1>Products</h1>

<?php if (!empty($productError)): ?>
    <p class="error"><?= e($productError) ?></p>
<?php endif; ?>

<section class="panel">
    <h2>Add product</h2>
    <form method="post" action="/admin/products.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
        <label>Description <input name="description" required></label>
        <label>Category <input name="category" required></label>
        <label>Price <input type="number" step="0.01" min="0.01" name="price" required></label>
        <label>Colour <input name="colour"></label>
        <label>Size <input name="size"></label>
        <label><input type="checkbox" name="is_available" value="1" checked> Available on storefront</label>
        <button type="submit">Add product</button>
    </form>
</section>

<section class="panel">
    <h2>Existing products</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Available</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <form method="post" action="/admin/products.php">
                        <td>
                            <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                            <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">
                            <input name="description" value="<?= e($product['description']) ?>" required>
                            <input name="colour" value="<?= e($product['colour']) ?>" placeholder="Colour">
                            <input name="size" value="<?= e($product['size']) ?>" placeholder="Size">
                        </td>
                        <td><input name="category" value="<?= e($product['category']) ?>" required></td>
                        <td><input type="number" step="0.01" min="0.01" name="price" value="<?= e($product['price']) ?>" required></td>
                        <td><input type="checkbox" name="is_available" value="1" <?= $product['is_available'] ? 'checked' : '' ?>></td>
                        <td><button type="submit">Save</button></td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php $view->footer(); ?>
