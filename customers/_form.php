<?php
// customers/_form.php - ginagamit ng add.php at edit.php
// Kailangan: $c (array ng data), $errors, $form_title
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
                <div class="col-12 col-md-6">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" maxlength="50" value="<?= e($c['first_name']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" maxlength="50" value="<?= e($c['last_name']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" maxlength="100" value="<?= e($c['email']) ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" maxlength="20" value="<?= e($c['phone']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" maxlength="150" value="<?= e($c['address']) ?>">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-navy"><i class="bi bi-check-lg"></i> Save</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
