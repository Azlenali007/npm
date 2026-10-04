<?php
/**
 * N.A Fresh Fruits & Coconuts - Logout
 */

require_once __DIR__ . '/config/database.php';

unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

header("Location: /");
exit;
