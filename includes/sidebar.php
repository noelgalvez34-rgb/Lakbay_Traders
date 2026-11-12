<?php
// includes/sidebar.php
$nav = [
    ['dashboard.php',         'Dashboard',   'bi-speedometer2', 'dashboard'],
    ['customers/index.php',   'Customers',   'bi-people',       'customers'],
    ['products/index.php',    'Products',    'bi-box-seam',     'products'],
    ['orders/index.php',      'Orders',      'bi-cart-check',   'orders'],
    ['order_items/index.php', 'Order Items',  'bi-list-check',   'order_items'],
    ['employees/index.php',   'Employees',   'bi-person-badge', 'employees'],
    ['reports/index.php',     'Reports',     'bi-bar-chart-line', 'reports'],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="brand"><i class="bi bi-truck"></i> LAKBAY TRADERS</div>
    <nav class="nav-list">
        <?php foreach ($nav as $n): ?>
            <a class="nav-item-link <?= $active === $n[3] ? 'active' : '' ?>" href="<?= $base . $n[0] ?>">
                <i class="bi <?= $n[2] ?>"></i> <span><?= $n[1] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <a class="nav-item-link logout" href="<?= $base ?>logout.php"><i class="bi bi-box-arrow-left"></i> <span>Logout</span></a>
</aside>
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
