<?php
/**
 * N.A Fresh Fruits & Coconuts - Wishlist Controller & Page
 */

require_once __DIR__ . '/config/database.php';

if (!is_logged_in()) {
    header("Location: /login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$db = getDB();
$userId = $_SESSION['user_id'];

// Handle Add to Wishlist
if (isset($_GET['add'])) {
    $prodId = (int)$_GET['add'];
    $ins = $db->prepare("INSERT IGNORE INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())");
    $ins->execute([$userId, $prodId]);
    header("Location: /account.php?tab=wishlist");
    exit;
}

// Handle Remove from Wishlist
if (isset($_GET['remove'])) {
    $prodId = (int)$_GET['remove'];
    $db->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?")->execute([$userId, $prodId]);
    header("Location: /account.php?tab=wishlist");
    exit;
}

header("Location: /account.php?tab=wishlist");
exit;
