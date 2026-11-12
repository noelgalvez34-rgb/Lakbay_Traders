<?php
// products/_form.php - ginagamit ng add.php at edit.php
?>
<div class="card">
    <div class="card-header"><?= e($form_title) ?></div>
    <div class="card-body">
        <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="product_name" class="form-control" maxlength="100" value="<?= e($p['product_name']) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" maxlength="255" value="<?= e($p['description']) ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Price (₱) *</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= e($p['price']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" class="form-control" step="1" min="0" value="<?= e($p['stock_quantity']) ?>" required>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-navy"><i class="bi bi-check-lg"></i> Save</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
