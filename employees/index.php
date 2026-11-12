<?php
// employees/index.php
require __DIR__ . '/../includes/auth.php';
require_login('../');

$base = '../';
$page_title = 'Employees';
$active = 'employees';

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare(
        'SELECT * FROM employees
         WHERE first_name LIKE ? OR last_name LIKE ? OR role LIKE ? OR CONCAT(first_name, " ", last_name) LIKE ?
         ORDER BY employee_id DESC'
    );
    $stmt->execute([$like, $like, $like, $like]);
} else {
    $stmt = $pdo->query('SELECT * FROM employees ORDER BY employee_id DESC');
}
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="get" class="flex-grow-1" style="max-width:420px;">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search employee..." value="<?= e($q) ?>">
            <button class="btn btn-navy"><i class="bi bi-search"></i></button>
        </div>
    </form>
    <a href="add.php" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Add Employee</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Role</th><th>Hire Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Walang nahanap na employee.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int)$r['employee_id'] ?></td>
                        <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                        <td><?= e($r['role']) ?></td>
                        <td><?= $r['hire_date'] ? e(date('M d, Y', strtotime($r['hire_date']))) : '-' ?></td>
                        <td class="actions">
                            <a href="view.php?id=<?= (int)$r['employee_id'] ?>" class="btn btn-sm btn-outline-navy" title="View"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?= (int)$r['employee_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Sigurado ka bang buburahin ang employee na ito?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$r['employee_id'] ?>">
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
