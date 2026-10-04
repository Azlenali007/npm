<?php
/**
 * N.A Fresh Fruits & Coconuts - SaaS Order Fulfillment & Dispatch
 * 100% Card-Based UI with real-time status transitions and direct phone/WhatsApp actions
 */

$adminTitle = 'Order Fulfillment & Dispatch';
require_once __DIR__ . '/header.php';

$db = getDB();
$msg = '';

// Handle Quick Status Transition via GET
if (isset($_GET['quick_status']) && isset($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
    $newStatus = trim($_GET['quick_status']);
    $allowedStatuses = ['Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];

    if (in_array($newStatus, $allowedStatuses)) {
        // If delivered, mark payment Paid if COD
        if ($newStatus === 'Delivered') {
            $db->prepare("UPDATE orders SET order_status = ?, payment_status = 'Paid', updated_at = NOW() WHERE id = ?")->execute([$newStatus, $orderId]);
            $db->prepare("UPDATE invoices SET payment_status = 'Paid' WHERE order_id = ?")->execute([$orderId]);
        } else {
            $db->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?")->execute([$newStatus, $orderId]);
        }

        // Notify user if logged in
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

        $msg = "Order #{$ord['order_number']} moved to '{$newStatus}'!";
    }
}

// Handle Detailed Status Update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['order_status']);
    $newPayStatus = trim($_POST['payment_status']);

    $upd = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
    $upd->execute([$newStatus, $newPayStatus, $orderId]);

    // Update invoice payment status if needed
    $db->prepare("UPDATE invoices SET payment_status = ? WHERE order_id = ?")->execute([$newPayStatus, $orderId]);

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

    $msg = "Order #{$ord['order_number']} updated successfully!";
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
      <h1 class="font-black text-2xl text-white tracking-tight">Jaora Order Fulfillment & Dispatch</h1>
      <p class="text-xs text-slate-400 mt-1">Track drink preparations, update rider dispatches, and manage customer invoices across Jaora.</p>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-800 text-emerald-300 text-xs font-bold flex items-center justify-between shadow-md">
      <span><?= e($msg) ?></span>
      <a href="/admin/orders.php" class="text-slate-400 hover:text-white">✕</a>
    </div>
  <?php endif; ?>

  <!-- Filter & Search Bar -->
  <div class="p-4 sm:p-5 rounded-3xl bg-[#09160e] border border-emerald-900/40 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
    
    <!-- Status Filter Tabs with Glowing Chips -->
    <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 scrollbar-none">
      <a href="/admin/orders.php" 
         class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= empty($statusFilter) ? 'bg-emerald-600 text-white shadow-md shadow-emerald-950/60 font-extrabold' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
        All Orders
      </a>
      <?php foreach (['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'] as $st): ?>
        <a href="/admin/orders.php?status=<?= urlencode($st) ?>" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap <?= $statusFilter === $st ? 'bg-emerald-600 text-white shadow-md shadow-emerald-950/60 font-extrabold' : 'text-slate-400 hover:text-white hover:bg-slate-900' ?>">
          <?= $st ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Search Form -->
    <form method="GET" class="flex items-center gap-2 w-full md:w-auto">
      <?php if (!empty($statusFilter)): ?>
        <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
      <?php endif; ?>
      <input type="text" 
             name="q" 
             value="<?= e($search) ?>" 
             placeholder="Search Order # or Mobile..." 
             class="px-4 py-2 rounded-xl bg-[#061009] border border-emerald-950 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 w-full sm:w-64 placeholder-slate-500">
      <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white text-xs font-extrabold shadow-sm transition-colors">
        Filter
      </button>
      <?php if (!empty($search) || !empty($statusFilter)): ?>
        <a href="/admin/orders.php" class="px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 text-xs hover:text-white" title="Clear Filters">✕</a>
      <?php endif; ?>
    </form>

  </div>

  <!-- Orders - Responsive Card Grid (100% Card-Based, No Table) -->
  <?php if (empty($orders)): ?>
    <div class="p-12 text-center rounded-3xl bg-[#09160e] border border-emerald-900/40 text-slate-400 text-sm shadow-xl">
      No orders match your filter criteria.
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <?php foreach ($orders as $ord): ?>
        <div class="p-6 rounded-3xl bg-[#09160e] border border-emerald-900/40 hover:border-emerald-600/60 transition-all duration-300 flex flex-col justify-between space-y-4 group shadow-xl">
          
          <!-- Top Row: Order ID, Date & Status -->
          <div class="flex items-start justify-between gap-2 pb-3 border-b border-emerald-950/80">
            <div>
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-mono font-black text-base text-white group-hover:text-emerald-400 transition-colors">
                  <?= e($ord['order_number']) ?>
                </span>
              </div>
              <span class="text-[11px] text-slate-400 block mt-0.5">
                <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
              </span>
            </div>

            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide
              <?php 
              if ($ord['order_status'] === 'Delivered') echo 'bg-emerald-950 text-emerald-300 border border-emerald-800';
              elseif ($ord['order_status'] === 'Cancelled') echo 'bg-rose-950 text-rose-300 border border-rose-800';
              elseif ($ord['order_status'] === 'Preparing') echo 'bg-blue-950 text-blue-300 border border-blue-800';
              else echo 'bg-amber-950 text-amber-300 border border-amber-800';
              ?>">
              ● <?= e($ord['order_status']) ?>
            </span>
          </div>

          <!-- Customer Info & Contact Actions -->
          <div class="space-y-2.5 text-xs">
            <div class="flex items-center justify-between">
              <span class="font-bold text-white text-sm"><?= e($ord['customer_name']) ?></span>
              <div class="flex items-center gap-1.5">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ord['customer_phone']) ?>" 
                   target="_blank" 
                   class="p-1.5 rounded-lg bg-emerald-950 text-emerald-300 hover:bg-emerald-900 border border-emerald-800/60 transition-colors"
                   title="WhatsApp Customer">
                  <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.962 1.199.662.589 1.221.771 1.394.858.173.086.274.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
                </a>
                <a href="tel:<?= e($ord['customer_phone']) ?>" 
                   class="px-2.5 py-1 rounded-lg bg-[#061009] border border-emerald-950 text-slate-300 hover:text-white text-[11px] font-mono flex items-center gap-1 transition-colors"
                   title="Call Customer">
                  <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  <span><?= e($ord['customer_phone']) ?></span>
                </a>
              </div>
            </div>

            <div class="flex items-center gap-1.5 text-slate-400 text-xs">
              <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
              <span class="font-medium text-slate-300 truncate">Delivery: <?= e($ord['area_name']) ?></span>
            </div>

            <?php if (!empty($ord['delivery_address'])): ?>
              <p class="text-[11px] text-slate-400 bg-[#061009] p-2.5 rounded-xl border border-emerald-950/80 line-clamp-2">
                <?= e($ord['delivery_address']) ?>
              </p>
            <?php endif; ?>
          </div>

          <!-- Total Amount & Payment Details -->
          <div class="pt-3 border-t border-emerald-950/80 flex items-center justify-between">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total</span>
              <span class="font-black text-xl text-emerald-400">
                <?= format_price($ord['total_amount']) ?>
              </span>
            </div>

            <div class="text-right">
              <span class="text-xs text-slate-300 font-semibold block"><?= e($ord['payment_method']) ?></span>
              <span class="text-[10px] font-extrabold <?= $ord['payment_status'] === 'Paid' ? 'text-emerald-400' : 'text-amber-400' ?>">
                ● <?= e($ord['payment_status']) ?>
              </span>
            </div>
          </div>

          <!-- Quick 1-Click Status Lifecycle Actions -->
          <div class="pt-2 border-t border-emerald-950/80">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Quick Advance:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
              <?php if ($ord['order_status'] === 'Pending' || $ord['order_status'] === 'Confirmed'): ?>
                <a href="/admin/orders.php?quick_status=Preparing&order_id=<?= $ord['id'] ?>" 
                   class="px-2.5 py-1 rounded-lg bg-blue-950/80 hover:bg-blue-900 border border-blue-800 text-blue-300 font-bold text-[10px] transition-colors">
                  ▶ Mark Preparing
                </a>
              <?php endif; ?>
              
              <?php if ($ord['order_status'] === 'Preparing'): ?>
                <a href="/admin/orders.php?quick_status=Out+for+Delivery&order_id=<?= $ord['id'] ?>" 
                   class="px-2.5 py-1 rounded-lg bg-amber-950/80 hover:bg-amber-900 border border-amber-800 text-amber-300 font-bold text-[10px] transition-colors">
                  🛵 Out for Delivery
                </a>
              <?php endif; ?>

              <?php if ($ord['order_status'] === 'Out for Delivery'): ?>
                <a href="/admin/orders.php?quick_status=Delivered&order_id=<?= $ord['id'] ?>" 
                   class="px-2.5 py-1 rounded-lg bg-emerald-950/80 hover:bg-emerald-900 border border-emerald-800 text-emerald-300 font-bold text-[10px] transition-colors">
                  ✓ Mark Delivered
                </a>
              <?php endif; ?>

              <a href="/admin/orders.php?view=<?= $ord['id'] ?>" 
                 class="ml-auto px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-300 text-[10px] font-bold transition-colors">
                Full Details →
              </a>
            </div>
          </div>

          <!-- Primary Actions -->
          <div class="grid grid-cols-2 gap-2 pt-1">
            <a href="/admin/orders.php?view=<?= $ord['id'] ?>" 
               class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-md shadow-emerald-950/40">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              <span>Manage Order</span>
            </a>
            
            <a href="/invoice.php?id=<?= $ord['id'] ?>" target="_blank" 
               class="py-2.5 px-3 rounded-xl bg-[#061009] hover:bg-slate-900 border border-emerald-950 text-slate-300 hover:text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors">
              <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span>View Invoice</span>
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- VIEW / MANAGE SINGLE ORDER MODAL -->
  <?php if ($viewOrder): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm">
      <div class="bg-[#09160e] border border-emerald-900/60 rounded-[2.5rem] p-6 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-6 shadow-2xl">
        
        <div class="flex items-center justify-between pb-4 border-b border-emerald-950">
          <div>
            <h2 class="font-black text-xl text-white">Order Details #<?= e($viewOrder['order_number']) ?></h2>
            <p class="text-xs text-slate-400">Placed on <?= date('d M Y, h:i A', strtotime($viewOrder['created_at'])) ?></p>
          </div>
          <a href="/admin/orders.php" class="w-9 h-9 rounded-xl bg-[#061009] border border-emerald-950 text-slate-400 hover:text-white flex items-center justify-center text-sm font-bold">✕</a>
        </div>

        <!-- Customer & Destination -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-[#061009] p-4 rounded-2xl border border-emerald-950">
          <div>
            <span class="font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Customer Info</span>
            <p class="font-extrabold text-white text-sm"><?= e($viewOrder['customer_name']) ?></p>
            <p class="text-emerald-400 font-mono mt-0.5"><?= e($viewOrder['customer_phone']) ?></p>
            <p class="text-slate-400"><?= e($viewOrder['customer_email']) ?></p>
          </div>
          <div>
            <span class="font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Delivery Address</span>
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

        <!-- Items Ordered (No Table, Clean List Card) -->
        <div class="space-y-2">
          <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider">Ordered Drinks</h3>
          <div class="divide-y divide-emerald-950 bg-[#061009] p-4 rounded-2xl border border-emerald-950 text-xs">
            <?php foreach ($viewItems as $it): ?>
              <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <img src="<?= e($it['product_image']) ?>" class="w-10 h-10 rounded-xl object-cover bg-slate-900 border border-emerald-950">
                  <div>
                    <h4 class="font-bold text-white"><?= e($it['product_name']) ?></h4>
                    <span class="text-slate-400"><?= (int)$it['quantity'] ?> × <?= format_price($it['unit_price']) ?></span>
                  </div>
                </div>
                <span class="font-black text-emerald-400 text-sm"><?= format_price($it['total_price']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Total Breakdown -->
        <div class="text-xs space-y-2 bg-[#061009] p-4 rounded-2xl border border-emerald-950">
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
          <div class="pt-2 border-t border-emerald-950 flex justify-between text-sm font-bold text-white">
            <span>Total Amount</span>
            <span class="text-emerald-400 font-black text-lg"><?= format_price($viewOrder['total_amount']) ?></span>
          </div>
        </div>

        <!-- Status Update Form -->
        <form method="POST" class="p-4 rounded-2xl bg-[#061009] border border-emerald-950 space-y-4">
          <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Update Order Lifecycle</label>
              <select name="order_status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-emerald-950 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <?php foreach (['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'] as $st): ?>
                  <option value="<?= $st ?>" <?= $viewOrder['order_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Update Payment Status</label>
              <select name="payment_status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-emerald-950 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="Pending" <?= $viewOrder['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending (COD)</option>
                <option value="Paid" <?= $viewOrder['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid (Received)</option>
                <option value="Failed" <?= $viewOrder['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
              </select>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <a href="/admin/orders.php" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white">Cancel</a>
            <button type="submit" name="update_order_status" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md transition-colors">
              Save Order Changes
            </button>
          </div>
        </form>

      </div>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
