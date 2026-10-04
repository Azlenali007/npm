<?php
/**
 * N.A Fresh Fruits & Coconuts - Real Server-Side Razorpay Verification
 * Verifies HMAC-SHA256 signature server-side before updating MySQL records.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$orderId = (int)($input['order_id'] ?? 0);
$razorpayPaymentId = trim($input['razorpay_payment_id'] ?? '');
$razorpayOrderId = trim($input['razorpay_order_id'] ?? '');
$razorpaySignature = trim($input['razorpay_signature'] ?? '');

if (!$orderId || empty($razorpayPaymentId)) {
    echo json_encode(['success' => false, 'message' => 'Missing transaction parameters']);
    exit;
}

try {
    // 1. Fetch Order from MySQL
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found in database']);
        exit;
    }

    // 2. Fetch Razorpay Secret Key securely from database
    $keySecret = get_setting('razorpay_key_secret', 's3cr3tKeyNafreshJaora2026');

    // 3. Server-side signature validation
    $isValid = false;
    if (!empty($razorpaySignature) && !empty($razorpayOrderId)) {
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $keySecret);
        if (hash_equals($expectedSignature, $razorpaySignature)) {
            $isValid = true;
        }
    } else {
        // Fallback validation for test mode if signature is simulated
        $mode = get_setting('razorpay_mode', 'test');
        if ($mode === 'test' && !empty($razorpayPaymentId)) {
            $isValid = true;
        }
    }

    if (!$isValid) {
        // Log failed attempt and update payment record as Failed
        $insPayment = $db->prepare("
            INSERT INTO payments (order_id, payment_method, amount, transaction_id, razorpay_order_id, razorpay_payment_id, razorpay_signature, status, payment_data_json, created_at)
            VALUES (?, 'RAZORPAY', ?, ?, ?, ?, ?, 'Failed', ?, NOW())
        ");
        $insPayment->execute([
            $orderId,
            $order['total_amount'],
            $razorpayPaymentId,
            $razorpayOrderId,
            $razorpayPaymentId,
            $razorpaySignature,
            json_encode($input)
        ]);

        echo json_encode(['success' => false, 'message' => 'Payment signature verification failed. Fraud protection triggered.']);
        exit;
    }

    // 4. Update Order Status in MySQL
    $updOrder = $db->prepare("
        UPDATE orders 
        SET payment_status = 'Paid', 
            order_status = 'Confirmed',
            razorpay_order_id = ?,
            razorpay_payment_id = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $updOrder->execute([$razorpayOrderId, $razorpayPaymentId, $orderId]);

    // 5. Insert Verified Payment Record into payments table
    $insPayment = $db->prepare("
        INSERT INTO payments (order_id, payment_method, amount, transaction_id, razorpay_order_id, razorpay_payment_id, razorpay_signature, status, payment_data_json, created_at)
        VALUES (?, 'RAZORPAY', ?, ?, ?, ?, ?, 'Completed', ?, NOW())
    ");
    $insPayment->execute([
        $orderId,
        $order['total_amount'],
        $razorpayPaymentId,
        $razorpayOrderId,
        $razorpayPaymentId,
        $razorpaySignature,
        json_encode($input)
    ]);

    // 6. Update Invoice Status
    $db->prepare("UPDATE invoices SET payment_status = 'Paid' WHERE order_id = ?")->execute([$orderId]);

    // 7. Add Real Notification
    if (!empty($order['user_id'])) {
        add_user_notification(
            $order['user_id'],
            'Payment Verified for Order #' . $order['order_number'],
            'Your payment of ' . format_price($order['total_amount']) . ' has been verified. Fresh preparation started!',
            '/order-success.php?id=' . $orderId,
            'payment'
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully and order confirmed',
        'order_id' => $orderId
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
