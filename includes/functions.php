<?php
if (!function_exists('format_rupiah')) {
    function format_rupiah($number) {
        return "Rp " . number_format($number, 0, ',', '.');
    }
}

/**
 * Escapes HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Basic Authentication Check
 */
function require_login() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: /index.php");
        exit;
    }
}

/**
 * Admin Authentication Check
 */
function require_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: /index.php");
        exit;
    }
}

function get_all_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    return $stmt->fetchAll();
}

function get_featured_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1");
    return $stmt->fetchAll();
}

function get_product_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_faqs_by_category($pdo) {
    $faqs = $pdo->query("SELECT * FROM faqs")->fetchAll();
    $grouped = [];
    foreach ($faqs as $faq) {
        $grouped[$faq['category']][] = $faq;
    }
    return $grouped;
}

function get_all_blog_posts($pdo) {
    return $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC")->fetchAll();
}
?>
