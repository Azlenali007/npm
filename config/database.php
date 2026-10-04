<?php
/**
 * N.A Fresh Fruits & Coconuts - Database & Core Configuration
 * Jaora, Madhya Pradesh, India
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Database Credentials
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'fresh_fruits');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a PDO database connection singleton
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If database does not exist, try to connect without dbname and create it
            try {
                $rootPdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e2) {
                // If it still fails, show clean error or redirect to installer
                if (!str_contains($_SERVER['REQUEST_URI'] ?? '', 'installer.php')) {
                    header('Location: /installer.php?error=' . urlencode('Please run the installer to setup MySQL: ' . $e2->getMessage()));
                    exit;
                }
                throw $e2;
            }
        }
    }
    return $pdo;
}

/**
 * Generates and returns CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies submitted CSRF token
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * HTML Escaping helper
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Currency formatting helper for INR ₹
 */
function format_price($amount) {
    return '₹' . number_format((float)$amount, 0);
}

/**
 * Fetch setting with cache & default
 */
function get_setting($key, $default = '') {
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Table might not exist yet
            return $default;
        }
    }
    return $settings[$key] ?? $default;
}

/**
 * Check if customer is logged in
 */
function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

/**
 * Return current logged in customer data
 */
function current_user() {
    if (!is_logged_in()) return null;
    static $cachedUser = null;
    if ($cachedUser === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $cachedUser = $stmt->fetch() ?: null;
    }
    return $cachedUser;
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in() {
    return !empty($_SESSION['admin_id']);
}

/**
 * Return current logged in admin
 */
function current_admin() {
    if (!is_admin_logged_in()) return null;
    static $cachedAdmin = null;
    if ($cachedAdmin === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $cachedAdmin = $stmt->fetch() ?: null;
    }
    return $cachedAdmin;
}

/**
 * Get or create shopping cart session ID
 */
function get_cart_session_id() {
    if (empty($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['cart_session_id'];
}

/**
 * Get Cart Data (items, subtotal, count)
 */
function get_cart_data() {
    $db = getDB();
    $sessionId = get_cart_session_id();
    $userId = $_SESSION['user_id'] ?? null;

    // Find cart
    if ($userId) {
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();
        if (!$cart) {
            $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$sessionId]);
            $cart = $stmt->fetch();
            if ($cart) {
                // Link cart to user
                $db->prepare("UPDATE carts SET user_id = ? WHERE id = ?")->execute([$userId, $cart['id']]);
            }
        }
    } else {
        $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$sessionId]);
        $cart = $stmt->fetch();
    }

    if (!$cart) {
        return ['cart_id' => null, 'items' => [], 'item_count' => 0, 'subtotal' => 0];
    }

    $cartId = $cart['id'];
    $stmt = $db->prepare("
        SELECT ci.id as item_id, ci.quantity, ci.unit_price, 
               p.id as product_id, p.name, p.slug, p.price, p.original_price, 
               p.image_url, p.unit_size, p.in_stock,
               c.name as category_name
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE ci.cart_id = ?
        ORDER BY ci.id DESC
    ");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();

    $itemCount = 0;
    $subtotal = 0;
    foreach ($items as $item) {
        $itemCount += (int)$item['quantity'];
        $subtotal += ((float)$item['unit_price'] * (int)$item['quantity']);
    }

    return [
        'cart_id' => $cartId,
        'items' => $items,
        'item_count' => $itemCount,
        'subtotal' => $subtotal
    ];
}

/**
 * Add real notification for user
 */
function add_user_notification($userId, $title, $message, $link = '', $type = 'info') {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, link, type, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $title, $message, $link, $type]);
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
    }
}
