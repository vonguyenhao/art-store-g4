<?php

require __DIR__ . '/../../src/bootstrap.php';

$auth = app('auth');
$csrf = app('csrf');
$session = app('session');
$productsRepository = app('products');
$view = app('view');
$auth->require();

function productImageStorageDirectory(): string
{
    return dirname(__DIR__, 2) . '/storage/product-images';
}

function safeProductImageFilename(?string $imagePath): ?string
{
    if (!$imagePath) {
        return null;
    }

    $filename = basename($imagePath);
    return preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename) ? $filename : null;
}

function uploadedProductImageFilename(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    if ((int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Product image must be 2MB or smaller.');
    }

    $originalExtension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($originalExtension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        throw new RuntimeException('Product image must use a .jpg, .jpeg, .png, or .webp file extension.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if (!is_uploaded_file($tmpName)) {
        throw new RuntimeException('Uploaded image could not be verified.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $tmpName) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!is_string($mimeType) || !isset($allowedTypes[$mimeType])) {
        throw new RuntimeException('Product image must be a JPG, PNG, or WebP file.');
    }

    $directory = productImageStorageDirectory();
    if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
        throw new RuntimeException('Product image storage is not writable.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
    $destination = $directory . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Product image could not be saved.');
    }

    return $filename;
}

function deleteProductImageIfUnused(?string $imagePath, App\Repository\ProductRepository $productsRepository): void
{
    $filename = safeProductImageFilename($imagePath);
    if (!$filename || $productsRepository->imagePathUseCount($filename) > 0) {
        return;
    }

    $path = productImageStorageDirectory() . '/' . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf->verify($_POST);

    $newImagePath = null;
    $oldImagePath = null;

    try {
        $productNo = (int) ($_POST['product_no'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);

        if ($description === '' || $category === '' || $price <= 0) {
            throw new RuntimeException('Description, category, and a positive price are required.');
        }

        $existingProduct = $productNo > 0 ? $productsRepository->find($productNo) : null;

        if ($productNo > 0 && !$existingProduct) {
            throw new RuntimeException('Product could not be found.');
        }

        $oldImagePath = $existingProduct['image_path'] ?? null;
        $newImagePath = uploadedProductImageFilename($_FILES['image'] ?? []);
        $imagePath = $newImagePath ?? $oldImagePath;

        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        $productsRepository->save([
            'product_no' => $productNo,
            'description' => $description,
            'category' => $category,
            'price' => $price,
            'colour' => trim($_POST['colour'] ?? ''),
            'size' => trim($_POST['size'] ?? ''),
            'image_path' => $imagePath,
            'is_available' => $isAvailable,
        ]);

        if ($newImagePath && $oldImagePath && $newImagePath !== $oldImagePath) {
            deleteProductImageIfUnused($oldImagePath, $productsRepository);
        }

        $session->flash($productNo > 0 ? 'Product updated.' : 'Product added.');
        redirect('/admin/products.php');
    } catch (Throwable $error) {
        if ($newImagePath) {
            $path = productImageStorageDirectory() . '/' . $newImagePath;
            if (is_file($path)) {
                unlink($path);
            }
        }

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

<section class="admin-page-header">
    <p><a href="/admin/index.php">Back to dashboard</a></p>
    <h1>Product management</h1>
    <p class="muted">Add artworks, update product details, upload images, and control customer availability.</p>
</section>

<?php if (!empty($productError)): ?>
    <p class="error"><?= e($productError) ?></p>
<?php endif; ?>

<section class="panel">
    <h2>Add product</h2>

    <form method="post" action="/admin/products.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">

        <label>Description <input name="description" required></label>
        <label>Category <input name="category" required></label>
        <label>Price <input type="number" step="0.01" min="0.01" name="price" required></label>
        <label>Colour <input name="colour"></label>
        <label>Size <input name="size"></label>
        <label>Artwork image <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></label>

        <label class="checkbox-label">
            <input type="checkbox" name="is_available" value="1" checked>
            Available for customers to order
        </label>

        <button type="submit">Add product</button>
    </form>
</section>

<section class="panel">
    <h2>Existing products</h2>

    <?php if (!$products): ?>
        <p class="muted">No products have been added yet.</p>
    <?php else: ?>
        <section class="grid admin-list-grid">
            <?php foreach ($products as $product): ?>
                <article class="card admin-card">
                    <form method="post" action="/admin/products.php" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                        <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">

                        <?php if ($product['image_path']): ?>
                            <img class="admin-product-image" src="/product_image.php?id=<?= (int) $product['product_no'] ?>" alt="<?= e($product['description']) ?>">
                        <?php else: ?>
                            <div class="admin-product-placeholder">No image</div>
                        <?php endif; ?>

                        <label>Description
                            <input name="description" value="<?= e($product['description']) ?>" required>
                        </label>

                        <label>Category
                            <input name="category" value="<?= e($product['category']) ?>" required>
                        </label>

                        <label>Price
                            <input type="number" step="0.01" min="0.01" name="price" value="<?= e($product['price']) ?>" required>
                        </label>

                        <label>Colour
                            <input name="colour" value="<?= e($product['colour']) ?>">
                        </label>

                        <label>Size
                            <input name="size" value="<?= e($product['size']) ?>">
                        </label>

                        <label>Replace image
                            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_available" value="1" <?= $product['is_available'] ? 'checked' : '' ?>>
                            Available for customers to order
                        </label>

                        <p class="muted">
                            Current status:
                            <strong><?= $product['is_available'] ? 'Available' : 'Unavailable' ?></strong>
                        </p>

                        <button type="submit">Save changes</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</section>

<?php $view->footer(); ?>