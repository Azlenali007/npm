<?php
/**
 * N.A Fresh Fruits & Coconuts - Shopping Cart API Endpoint
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';
$db = getDB();
$sessionId = get_cart_session_id();
$userId = $_SESSION['user_id'] ?? null;

// Read JSON input if POST
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

/**
 * Helper to ensure cart exists in MySQL
 */
function get_or_create_cart_id($db, $sessionId, $userId) {
    if ($userId) {
        $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();
        if ($cart) return (int)$cart['id'];
    }

    $stmt = $db->prepare("SELECT id FROM carts WHERE session_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$sessionId]);
    $cart = $stmt->fetch();
    if ($cart) {
        if ($userId) {
            $db->prepare("UPDATE carts SET user_id = ? WHERE id = ?")->execute([$userId, $cart['id']]);
        }
        return (int)$cart['id'];
    }

    $stmt = $db->prepare("INSERT INTO carts (user_id, session_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$userId, $sessionId]);
    return (int)$db->lastInsertId();
}

try {
    switch ($action) {
        case 'get':
            $cartData = get_cart_data();
            echo json_encode([
                'success' => true,
                'items' => $cartData['items'],
                'item_count' => $cartData['item_count'],
                'subtotal' => $cartData['subtotal']
            ]);
            break;

        case 'add':
            $productId = (int)($input['product_id'] ?? 0);
            $qty = max(1, (int)($input['quantity'] ?? 1));

            if (!$productId) {
                echo json_encode(['success' => false, 'message' => 'Invalid product']);
                exit;
            }

            // Verify product
            $stmt = $db->prepare("SELECT id, name, price, in_stock FROM products WHERE id = ? AND status = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }

            if (!$product['in_stock']) {
                echo json_encode(['success' => false, 'message' => 'Sorry, this product is currently out of stock']);
                exit;
            }

            $cartId = get_or_create_cart_id($db, $sessionId, $userId);

            // Check if item already in cart
            $stmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cartId, $productId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $newQty = $existing['quantity'] + $qty;
                $stmt = $db->prepare("UPDATE cart_items SET quantity = ?, unit_price = ? WHERE id = ?");
                $stmt->execute([$newQty, $product['price'], $existing['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, unit_price, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$cartId, $productId, $qty, $product['price']]);
            }

            $cartData = get_cart_data();
            echo json_encode([
                'success' => true,
                'message' => 'Added ' . $product['name'] . ' to cart!',
                'items' => $cartData['items'],
                'item_count' => $cartData['item_count'],
                'subtotal' => $cartData['subtotal']
            ]);
            break;

        case 'update':
            $productId = (int)($input['product_id'] ?? 0);
            $qty = (int)($input['quantity'] ?? 0);

            $cartId = get_or_create_cart_id($db, $sessionId, $userId);

            if ($qty <= 0) {
                $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
                $stmt->execute([$cartId, $productId]);
            } else {
                $stmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                $stmt->execute([$qty, $cartId, $productId]);
            }

            $cartData = get_cart_data();
            echo json_encode([
                'success' => true,
                'items' => $cartData['items'],
                'item_count' => $cartData['item_count'],
                'subtotal' => $cartData['subtotal']
            ]);
            break;

        case 'remove':
            $productId = (int)($input['product_id'] ?? 0);
            $cartId = get_or_create_cart_id($db, $sessionId, $userId);

            $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cartId, $productId]);

            $cartData = get_cart_data();
            echo json_encode([
                'success' => true,
                'items' => $cartData['items'],
                'item_count' => $cartData['item_count'],
                'subtotal' => $cartData['subtotal']
            ]);
            break;

        case 'apply_coupon':
            $code = strtoupper(trim($input['code'] ?? ''));
            if (!$code) {
                echo json_encode(['success' => false, 'message' => 'Please enter coupon code']);
                exit;
            }

            $cartData = get_cart_data();
            $subtotal = $cartData['subtotal'];

            $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();

            if (!$coupon) {
                echo json_encode(['success' => false, 'message' => 'Invalid coupon code']);
                exit;
            }

            if ($subtotal < $coupon['min_order_amount']) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Minimum order amount for this coupon is ' . format_price($coupon['min_order_amount'])
                ]);
                exit;
            }

            // Calculate discount
            $discount = 0;
            if ($coupon['discount_type'] === 'percentage') {
                $discount = ($subtotal * $coupon['discount_value']) / 100;
                if (!empty($coupon['max_discount']) && $discount > $coupon['max_discount']) {
                    $discount = $coupon['max_discount'];
                }
            } else {
                $discount = min($subtotal, (float)$coupon['discount_value']);
            }

            $_SESSION['applied_coupon'] = [
                'code' => $coupon['code'],
                'discount' => $discount,
                'type' => $coupon['discount_type'],
                'value' => $coupon['discount_value']
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Coupon ' . $coupon['code'] . ' applied successfully!',
                'discount' => $discount,
                'coupon' => $_SESSION['applied_coupon']
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
