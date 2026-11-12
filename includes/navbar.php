<?php
// includes/navbar.php - top bar
?>
<header class="topbar">
    <button class="btn btn-navy d-lg-none" id="sidebar-toggle" type="button" aria-label="Menu"><i class="bi bi-list"></i></button>
    <h1 class="page-title"><?= e($page_title) ?></h1>
    <div class="ms-auto user-box">
        <i class="bi bi-person-circle"></i>
        <span class="d-none d-sm-inline"><?= e($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin') ?></span>
    </div>
</header>
