<?php
// employees/_form.php - ginagamit ng add.php at edit.php
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
                    <input type="text" name="first_name" class="form-control" maxlength="50" value="<?= e($emp['first_name']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" maxlength="50" value="<?= e($emp['last_name']) ?>" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Role</label>
                    <input type="text" name="role" class="form-control" maxlength="50" value="<?= e($emp['role']) ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" value="<?= e($emp['hire_date']) ?>">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-navy"><i class="bi bi-check-lg"></i> Save</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
