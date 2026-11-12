<?php
// includes/auth.php
// Session, login check, CSRF protection, at mga helper function ng buong system.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/../config/database.php';

const LOW_STOCK_LIMIT = 10;
const ORDER_STATUSES  = ['Pending', 'Processing', 'Completed', 'Cancelled'];

// ---------- Basic helpers ----------

// Ligtas na pag-print ng text (proteksyon laban sa XSS)
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value)
{
    return '₱' . number_format((float)$value, 2);
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function str_len($s)
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

function get_id($key = 'id')
{
    return (int)($_GET[$key] ?? 0);
}

// ---------- Login protection ----------

function require_login($base = '')
{
    if (empty($_SESSION['user_id'])) {
        redirect($base . 'login.php');
    }
}

// ---------- Flash messages ----------

function flash_set($type, $msg)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_show()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
            . e($f['msg'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// ---------- CSRF ----------

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check()
{
    $t = $_POST['csrf_token'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        die('Invalid security token (CSRF). Bumalik sa nakaraang page, i-refresh, at subukan ulit.');
    }
}

// ---------- Status badge ----------

function status_badge($status)
{
    $map = [
        'Pending'    => 'bg-warning text-dark',
        'Processing' => 'bg-info text-dark',
        'Completed'  => 'bg-success',
        'Cancelled'  => 'bg-danger',
    ];
    $cls = $map[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $cls . '">' . e($status) . '</span>';
}

// ---------- Validation ----------

function validate_customer($c)
{
    $err = [];
    if ($c['first_name'] === '' || str_len($c['first_name']) > 50) $err[] = 'First name ay required (hanggang 50 letra).';
    if ($c['last_name'] === ''  || str_len($c['last_name']) > 50)  $err[] = 'Last name ay required (hanggang 50 letra).';
    if ($c['email'] !== '' && (!filter_var($c['email'], FILTER_VALIDATE_EMAIL) || str_len($c['email']) > 100)) $err[] = 'Hindi valid ang email.';
    if ($c['phone'] !== '' && (!preg_match('/^[0-9+\-\s()]+$/', $c['phone']) || str_len($c['phone']) > 20)) $err[] = 'Phone ay numbers lang (hanggang 20).';
    if (str_len($c['address']) > 150) $err[] = 'Address ay hanggang 150 letra lang.';
    return $err;
}

function validate_product($p)
{
    $err = [];
    if ($p['product_name'] === '' || str_len($p['product_name']) > 100) $err[] = 'Product name ay required (hanggang 100 letra).';
    if (str_len($p['description']) > 255) $err[] = 'Description ay hanggang 255 letra lang.';
    if (!is_numeric($p['price']) || (float)$p['price'] < 0 || (float)$p['price'] > 99999999.99) $err[] = 'Price ay dapat valid na numero (0 pataas).';
    if (filter_var($p['stock_quantity'], FILTER_VALIDATE_INT) === false || (int)$p['stock_quantity'] < 0) $err[] = 'Stock ay dapat buong numero (0 pataas).';
    return $err;
}

function validate_employee($e)
{
    $err = [];
    if ($e['first_name'] === '' || str_len($e['first_name']) > 50) $err[] = 'First name ay required (hanggang 50 letra).';
    if ($e['last_name'] === ''  || str_len($e['last_name']) > 50)  $err[] = 'Last name ay required (hanggang 50 letra).';
    if (str_len($e['role']) > 50) $err[] = 'Role ay hanggang 50 letra lang.';
    if ($e['hire_date'] !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $e['hire_date']);
        if (!$d || $d->format('Y-m-d') !== $e['hire_date']) $err[] = 'Hindi valid ang hire date.';
    }
    return $err;
}
