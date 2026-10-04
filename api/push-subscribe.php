<?php
/**
 * N.A Fresh Fruits & Coconuts - Web Push Subscription API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['endpoint'])) {
    echo json_encode(['success' => false, 'message' => 'Missing endpoint']);
    exit;
}

$db = getDB();
$userId = $_SESSION['user_id'] ?? null;
$endpoint = trim($input['endpoint']);
$keys = json_encode($input['keys'] ?? []);
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $stmt = $db->prepare("SELECT id FROM push_subscriptions WHERE endpoint = ?");
    $stmt->execute([$endpoint]);
    $existing = $stmt->fetch();

    if ($existing) {
        $db->prepare("UPDATE push_subscriptions SET user_id = ?, keys_json = ?, user_agent = ? WHERE id = ?")
           ->execute([$userId, $keys, $userAgent, $existing['id']]);
    } else {
        $db->prepare("INSERT INTO push_subscriptions (user_id, endpoint, keys_json, user_agent, created_at) VALUES (?, ?, ?, ?, NOW())")
           ->execute([$userId, $endpoint, $keys, $userAgent]);
    }

    echo json_encode(['success' => true, 'message' => 'Push subscription registered successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
