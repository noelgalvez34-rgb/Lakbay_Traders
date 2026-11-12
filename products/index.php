<?php
// products/index.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Products';
$active = 'products';

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_name LIKE ? OR description LIKE ? ORDER BY product_id DESC');
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM products ORDER BY product_id DESC');
}
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="get" class="flex-grow-1" style="max-width:420px;">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search product..." value="<?= e($q) ?>">
            <button class="btn btn-navy"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <a href="add.php" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Add Product</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>ID</th><th>Product</th><th>Description</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Walang nahanap na product.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): $stock = (int)$r['stock_quantity']; ?>
                    <tr>
                        <td><?= (int)$r['product_id'] ?></td>
                        <td><?= e($r['product_name']) ?></td>
                        <td><?= e($r['description']) ?></td>
                        <td><?= money($r['price']) ?></td>
                        <td>
                            <?= $stock ?>
                            <?php if ($stock === 0): ?>
                                <span class="badge bg-danger">Out of stock</span>
                            <?php elseif ($stock <= LOW_STOCK_LIMIT): ?>
                                <span class="badge bg-warning text-dark">Low stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="edit.php?id=<?= (int)$r['product_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Sigurado ka bang buburahin ang product na ito?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$r['product_id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
