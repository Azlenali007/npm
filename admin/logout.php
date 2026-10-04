<?php
require_once __DIR__ . '/../config/database.php';
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);
header("Location: /admin/login.php");
exit;
