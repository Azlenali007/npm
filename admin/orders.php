<?php
/**
 * N.A Fresh Fruits & Coconuts - Admin Order Management
 */

$adminTitle = 'Order Fulfillment & Dispatch';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['order_status']);
    $newPayStatus = trim($_POST['payment_status']);

    $upd = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
    $upd->execute([$newStatus, $newPayStatus, $orderId]);

    // Update invoice payment status if needed
    $db->prepare("UPDATE invoices SET payment_status = ? WHERE order_id = ?")->execute([$newPayStatus, $orderId]);

    // Send Real Notification to User if logged in
    $stmt = $db->prepare("SELECT user_id, order_number FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $ord = $stmt->fetch();
    if (!empty($ord['user_id'])) {
        add_user_notification(
            $ord['user_id'],
            "Order #{$ord['order_number']} is {$newStatus}",
            "Your fresh order status has been updated to {$newStatus}.",
            "/order-success.php?id={$orderId}",
            'order'
        );
    }

    $msg = "Order #{$ord['order_number']} status updated to '{$newStatus}'!";
}

// Filters & Search
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND order_status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (order_number LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Handle Order View Modal (Single Order)
$viewOrderId = (int)($_GET['view'] ?? 0);
$viewOrder = null;
$viewItems = [];
if ($viewOrderId > 0) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$viewOrderId]);
    $viewOrder = $stmt->fetch();
    if ($viewOrder) {
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$viewOrderId]);
        $viewItems = $stmt->fetchAll();
    }
}
?>

<div class="space-y-6">
  
  <!-- Header Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="font-bold text-2xl text-white">Jaora Order Fulfillment</h1>
      <p class="text-xs text-slate-400 mt-1">Track drink preparations, update rider dispatches, and manage customer invoices.</p>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold">
      <?= e($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Filter & Search Bar -->
  <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
    
    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 scrollbar-none">
      <a href="/admin/orders.php" 
         class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors <?= empty($statusFilter) ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
        All
      </a>
      <?php foreach (['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'] as $st): ?>
        <a href="/admin/orders.php?status=<?= urlencode($st) ?>" 
           class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors whitespace-nowrap <?= $statusFilter === $st ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
          <?= $st ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Search Form -->
    <form method="GET" class="flex items-center gap-2 w-full md:w-auto">
      <?php if (!empty($statusFilter)): ?>
        <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
      <?php endif; ?>
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search Order # or Mobile" class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full sm:w-64">
      <button type="submit" class="px-4 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-700">Filter</button>
    </form>

  </div>

  <!-- Orders Table -->
  <div class="rounded-3xl bg-slate-950 border border-slate-800 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-900/40">
            <th class="py-3.5 px-4">Order #</th>
            <th class="py-3.5 px-3">Date</th>
            <th class="py-3.5 px-3">Customer</th>
            <th class="py-3.5 px-3">Jaora Area</th>
            <th class="py-3.5 px-3">Total Amount</th>
            <th class="py-3.5 px-3">Payment</th>
            <th class="py-3.5 px-3">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="8" class="py-8 text-center text-slate-500">
                No orders match your filter criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $ord): ?>
              <tr class="hover:bg-slate-900/60 transition-colors">
                <td class="py-3 px-4 font-mono font-bold text-white">
                  <?= e($ord['order_number']) ?>
                </td>
                <td class="py-3 px-3 text-slate-400">
                  <?= date('d M, h:i A', strtotime($ord['created_at'])) ?>
                </td>
                <td class="py-3 px-3">
                  <span class="font-semibold text-white block"><?= e($ord['customer_name']) ?></span>
                  <a href="tel:<?= e($ord['customer_phone']) ?>" class="text-[11px] text-emerald-400 hover:underline"><?= e($ord['customer_phone']) ?></a>
                </td>
                <td class="py-3 px-3 text-slate-300">
                  <?= e($ord['area_name']) ?>
                </td>
                <td class="py-3 px-3 font-serif font-bold text-emerald-400 text-sm">
                  <?= format_price($ord['total_amount']) ?>
                </td>
                <td class="py-3 px-3">
                  <span class="font-medium text-slate-200 block"><?= e($ord['payment_method']) ?></span>
                  <span class="text-[10px] <?= $ord['payment_status'] === 'Paid' ? 'text-emerald-400' : 'text-amber-400' ?>">
                    <?= e($ord['payment_status']) ?>
                  </span>
                </td>
                <td class="py-3 px-3">
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold 
                    <?php 
                    if ($ord['order_status'] === 'Delivered') echo 'bg-emerald-950 text-emerald-300 border border-emerald-800';
                    elseif ($ord['order_status'] === 'Cancelled') echo 'bg-rose-950 text-rose-300 border border-rose-800';
                    elseif ($ord['order_status'] === 'Preparing') echo 'bg-blue-950 text-blue-300 border border-blue-800';
                    else echo 'bg-amber-950 text-amber-300 border border-amber-800';
                    ?>">
                    <?= e($ord['order_status']) ?>
                  </span>
                </td>
                <td class="py-3 px-4 text-right space-x-2">
                  <a href="/admin/orders.php?view=<?= $ord['id'] ?>" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors">
                    Manage
                  </a>
                  <a href="/invoice.php?id=<?= $ord['id'] ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition-colors">
                    Invoice
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- VIEW / MANAGE SINGLE ORDER MODAL -->
  <?php if ($viewOrder): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs">
      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-6">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
          <div>
            <h2 class="font-bold text-xl text-white">Order Details #<?= e($viewOrder['order_number']) ?></h2>
            <p class="text-xs text-slate-400">Placed on <?= date('d M Y, h:i A', strtotime($viewOrder['created_at'])) ?></p>
          </div>
          <a href="/admin/orders.php" class="p-2 text-slate-400 hover:text-white text-lg">✕</a>
        </div>

        <!-- Customer & Destination -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-950 p-4 rounded-2xl border border-slate-800">
          <div>
            <span class="font-bold text-slate-500 uppercase block mb-1">Customer Info</span>
            <p class="font-bold text-white text-sm"><?= e($viewOrder['customer_name']) ?></p>
            <p class="text-emerald-400"><?= e($viewOrder['customer_phone']) ?></p>
            <p class="text-slate-400"><?= e($viewOrder['customer_email']) ?></p>
          </div>
          <div>
            <span class="font-bold text-slate-500 uppercase block mb-1">Delivery Address</span>
            <p class="text-slate-200"><?= nl2br(e($viewOrder['delivery_address'])) ?></p>
            <p class="text-slate-400 mt-1">Landmark: <?= e($viewOrder['landmark'] ?: 'None') ?></p>
            <p class="font-bold text-emerald-400 mt-1">Area: <?= e($viewOrder['area_name']) ?></p>
          </div>
        </div>

        <?php if (!empty($viewOrder['notes'])): ?>
          <div class="p-3 bg-amber-950/40 border border-amber-800/60 rounded-xl text-xs text-amber-200">
            <span class="font-bold">Customer Notes:</span> <?= e($viewOrder['notes']) ?>
          </div>
        <?php endif; ?>

        <!-- Items Ordered -->
        <div class="space-y-2">
          <h3 class="font-bold text-xs text-slate-400 uppercase tracking-wider">Ordered Drinks</h3>
          <div class="divide-y divide-slate-800 bg-slate-950 p-4 rounded-2xl border border-slate-800 text-xs">
            <?php foreach ($viewItems as $it): ?>
              <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <img src="<?= e($it['product_image']) ?>" class="w-9 h-9 rounded-lg object-cover bg-slate-800">
                  <div>
                    <h4 class="font-bold text-white"><?= e($it['product_name']) ?></h4>
                    <span class="text-slate-400"><?= (int)$it['quantity'] ?> × <?= format_price($it['unit_price']) ?></span>
                  </div>
                </div>
                <span class="font-bold text-emerald-400"><?= format_price($it['total_price']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Total Breakdown -->
        <div class="text-xs space-y-1.5 bg-slate-950 p-4 rounded-2xl border border-slate-800">
          <div class="flex justify-between text-slate-400">
            <span>Subtotal</span>
            <span><?= format_price($viewOrder['subtotal']) ?></span>
          </div>
          <div class="flex justify-between text-slate-400">
            <span>Delivery Fee</span>
            <span><?= format_price($viewOrder['delivery_charge']) ?></span>
          </div>
          <?php if ($viewOrder['discount_amount'] > 0): ?>
            <div class="flex justify-between text-emerald-400 font-bold">
              <span>Discount</span>
              <span>- <?= format_price($viewOrder['discount_amount']) ?></span>
            </div>
          <?php endif; ?>
          <div class="pt-2 border-t border-slate-800 flex justify-between text-sm font-bold text-white">
            <span>Total Amount</span>
            <span class="text-emerald-400 font-serif text-base"><?= format_price($viewOrder['total_amount']) ?></span>
          </div>
        </div>

        <!-- Status Update Form -->
        <form method="POST" class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 space-y-4">
          <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Update Order Lifecycle</label>
              <select name="order_status" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-600 text-xs text-white">
                <?php foreach (['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'] as $st): ?>
                  <option value="<?= $st ?>" <?= $viewOrder['order_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Update Payment Status</label>
              <select name="payment_status" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-600 text-xs text-white">
                <?php foreach (['Pending', 'Paid', 'Failed'] as $pst): ?>
                  <option value="<?= $pst ?>" <?= $viewOrder['payment_status'] === $pst ? 'selected' : '' ?>><?= $pst ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-between pt-2">
            <a href="/invoice.php?id=<?= $viewOrder['id'] ?>" target="_blank" class="text-xs text-emerald-400 font-bold hover:underline">
              Open Full Invoice →
            </a>
            <button type="submit" name="update_order_status" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs">
              Save Status Updates
            </button>
          </div>
        </form>

      </div>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
