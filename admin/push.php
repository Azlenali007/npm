<?php
/**
 * N.A Fresh Fruits & Coconuts - Push Notifications Broadcaster
 */

$adminTitle = 'Push Notification Dispatcher';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

// Handle Broadcast
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_broadcast'])) {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $link = trim($_POST['link'] ?? '/products.php');
    $type = trim($_POST['type'] ?? 'offer');

    if (!empty($title) && !empty($message)) {
        // 1. Insert into general notifications for all users
        $ins = $db->prepare("
            INSERT INTO notifications (user_id, title, message, link, type, is_read, created_at)
            VALUES (NULL, ?, ?, ?, ?, 0, NOW())
        ");
        $ins->execute([$title, $message, $link, $type]);

        // 2. Fetch all registered user accounts and create user-level notifications
        $users = $db->query("SELECT id FROM users")->fetchAll();
        $insUserNotif = $db->prepare("
            INSERT INTO notifications (user_id, title, message, link, type, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        foreach ($users as $u) {
            $insUserNotif->execute([$u['id'], $title, $message, $link, $type]);
        }

        // 3. Count push subscriptions registered
        $subsCount = (int)$db->query("SELECT COUNT(*) FROM push_subscriptions")->fetchColumn();

        $msg = "Notification broadcast sent successfully! Dispatched to " . count($users) . " customer account(s) and {$subsCount} browser subscriber(s).";
    }
}

// Fetch Subscribers Count
$subsCount = (int)$db->query("SELECT COUNT(*) FROM push_subscriptions")->fetchColumn();
$recentNotifs = $db->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 10")->fetchAll();
?>

<div class="space-y-6 max-w-4xl">
  
  <div>
    <h1 class="font-bold text-2xl text-white">Push Notifications & Broadcasts</h1>
    <p class="text-xs text-slate-400 mt-1">Send real notification alerts to customers for fresh batches, limited offers, and delivery updates.</p>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div class="p-5 rounded-3xl bg-slate-950 border border-slate-800 space-y-1">
      <span class="text-xs font-bold text-slate-400 uppercase">Subscribed Devices</span>
      <div class="font-bold text-2xl text-emerald-400"><?= $subsCount ?></div>
      <p class="text-[11px] text-slate-500">Registered browser endpoints</p>
    </div>
  </div>

  <!-- Send Form -->
  <div class="p-6 sm:p-8 rounded-3xl bg-slate-950 border border-slate-800 space-y-4 text-xs">
    <h2 class="font-bold text-base text-white pb-3 border-b border-slate-800">Dispatch Push Message</h2>
    <form method="POST" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Alert Headline *</label>
          <input type="text" name="title" required placeholder="e.g. Fresh Sweet Tender Coconuts Just Arrived!" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
        </div>
        <div>
          <label class="block font-bold text-slate-300 uppercase mb-1">Notification Category</label>
          <select name="type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
            <option value="offer">Special Offer / Coupon</option>
            <option value="new_product">New Drink Arrival</option>
            <option value="system">Store Notice / Timing</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block font-bold text-slate-300 uppercase mb-1">Notification Body *</label>
        <textarea name="message" required rows="2" placeholder="e.g. Today's tender coconuts have extra sweet water and thick malai. Order now for 20-min delivery in Jaora!" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white"></textarea>
      </div>

      <div>
        <label class="block font-bold text-slate-300 uppercase mb-1">Destination Click Link</label>
        <input type="text" name="link" value="/products.php" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white">
      </div>

      <div class="pt-2 flex justify-end">
        <button type="submit" name="send_broadcast" class="px-8 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors">
          Send Push Notification
        </button>
      </div>
    </form>
  </div>

  <!-- Recent Notifications Log -->
  <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 space-y-4">
    <h3 class="font-bold text-sm text-white">Recent Dispatched Alerts</h3>
    <div class="divide-y divide-slate-800 text-xs">
      <?php foreach ($recentNotifs as $rn): ?>
        <div class="py-3 flex items-start justify-between gap-4">
          <div>
            <span class="font-bold text-white"><?= e($rn['title']) ?></span>
            <p class="text-slate-400 mt-0.5"><?= e($rn['message']) ?></p>
            <span class="text-[10px] text-slate-500 mt-1 block"><?= date('d M Y, h:i A', strtotime($rn['created_at'])) ?></span>
          </div>
          <span class="px-2 py-0.5 rounded bg-slate-900 text-slate-400 text-[10px] uppercase font-bold shrink-0">
            <?= e($rn['type']) ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
