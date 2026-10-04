<?php
/**
 * N.A Fresh Fruits & Coconuts - Order Confirmation & Live Tracking
 */

require_once __DIR__ . '/config/database.php';

$orderId = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: /");
    exit;
}

// Fetch Order Items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Fetch Associated Invoice
$stmt = $db->prepare("SELECT id, invoice_number FROM invoices WHERE order_id = ?");
$stmt->execute([$orderId]);
$invoice = $stmt->fetch();

$bizPhone = get_setting('phone', '+91 98260 12345');
$bizWhatsApp = get_setting('whatsapp', '919826012345');

// Status progression calculation
$statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered'];
$currentStatus = $order['order_status'];
$isCancelled = ($currentStatus === 'Cancelled');
$statusIdx = array_search($currentStatus, $statuses);
if ($statusIdx === false) $statusIdx = 0;

$pageTitle = 'Order Confirmed #' . $order['order_number'] . ' – N.A Fresh Fruits & Coconuts';
$pageDesc = 'Your tender coconut and fresh juice order has been received and is being prepared in Jaora.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-coconut-cream min-h-screen py-10">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Top Success Banner -->
    <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 shadow-xs text-center space-y-4 mb-8">
      <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-2xl shadow-sm">
        ✓
      </div>
      <div class="space-y-1">
        <span class="text-xs font-bold text-brand-700 uppercase tracking-wider">Order Placed Successfully</span>
        <h1 class="font-serif font-bold text-3xl sm:text-4xl text-brand-950 tracking-tight">
          Thank You, <?= e($order['customer_name']) ?>!
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
          We have received your order <span class="font-bold text-slate-800">#<?= e($order['order_number']) ?></span>. Our team in Jaora has started fresh preparation.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
        <?php if ($invoice): ?>
          <a href="/invoice.php?id=<?= $order['id'] ?>" target="_blank" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>View & Print Invoice</span>
          </a>
        <?php endif; ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $bizWhatsApp) ?>?text=Hello%20NA%20Fresh%20Fruits,%20regarding%20my%20order%20#<?= urlencode($order['order_number']) ?>" 
           target="_blank" 
           class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors flex items-center gap-1.5 shadow-xs">
          <span>Track via WhatsApp</span>
        </a>
      </div>
    </div>

    <!-- Live Status Progress Tracker -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs mb-8 space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="font-serif font-bold text-lg text-slate-900">Order Progress</h2>
        <span class="px-3 py-1 rounded-full text-xs font-bold <?= $isCancelled ? 'bg-red-100 text-red-700' : 'bg-brand-100 text-brand-800' ?>">
          Status: <?= e($order['order_status']) ?>
        </span>
      </div>

      <?php if ($isCancelled): ?>
        <div class="p-4 rounded-2xl bg-red-50 text-red-700 text-xs font-bold">
          This order has been cancelled. If you need any assistance, please call our Jaora counter at <?= e($bizPhone) ?>.
        </div>
      <?php else: ?>
        <!-- 5 Steps Timeline -->
        <div class="relative py-4">
          <div class="hidden sm:block absolute top-1/2 left-0 right-0 h-1 bg-slate-100 -translate-y-1/2 z-0"></div>
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 relative z-10">
            <?php foreach ($statuses as $idx => $st): ?>
              <?php $done = ($idx <= $statusIdx); ?>
              <div class="flex flex-col items-center text-center space-y-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs transition-colors <?= $done ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' ?>">
                  <?= $done ? '✓' : ($idx + 1) ?>
                </div>
                <div>
                  <span class="block text-xs font-bold <?= $done ? 'text-brand-900' : 'text-slate-400' ?>"><?= $st ?></span>
                  <span class="text-[10px] text-slate-400">
                    <?php 
                    if ($st === 'Confirmed') echo 'Received';
                    elseif ($st === 'Preparing') echo 'Cold Pressing';
                    elseif ($st === 'Out for Delivery') echo 'Rider Dispatched';
                    elseif ($st === 'Delivered') echo 'At Doorstep';
                    else echo 'Initiated';
                    ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Order Summary & Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      
      <!-- Items List -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
        <h3 class="font-serif font-bold text-base text-slate-900 pb-3 border-b border-slate-100">
          Items Ordered
        </h3>
        <div class="space-y-3">
          <?php foreach ($items as $item): ?>
            <div class="flex items-center justify-between gap-3 text-xs">
              <div class="flex items-center gap-3">
                <img src="<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>" class="w-10 h-10 rounded-xl object-cover bg-slate-100 shrink-0">
                <div>
                  <h4 class="font-bold text-slate-800"><?= e($item['product_name']) ?></h4>
                  <div class="text-[11px] text-slate-500">Qty: <?= (int)$item['quantity'] ?> × <?= format_price($item['unit_price']) ?></div>
                </div>
              </div>
              <span class="font-bold text-slate-900"><?= format_price($item['total_price']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2 text-xs text-slate-600">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span class="font-bold text-slate-900"><?= format_price($order['subtotal']) ?></span>
          </div>
          <div class="flex justify-between">
            <span>Delivery Fee (<?= e($order['area_name']) ?>)</span>
            <span class="font-bold text-slate-900"><?= format_price($order['delivery_charge']) ?></span>
          </div>
          <?php if ($order['discount_amount'] > 0): ?>
            <div class="flex justify-between text-emerald-700 font-bold">
              <span>Discount</span>
              <span>- <?= format_price($order['discount_amount']) ?></span>
            </div>
          <?php endif; ?>
          <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-bold text-slate-900">
            <span class="font-serif">Total Amount</span>
            <span class="font-serif text-lg text-brand-900"><?= format_price($order['total_amount']) ?></span>
          </div>
        </div>
      </div>

      <!-- Delivery & Payment Info -->
      <div class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
          <h3 class="font-serif font-bold text-base text-slate-900 pb-3 border-b border-slate-100">
            Delivery Destination
          </h3>
          <div class="text-xs text-slate-600 space-y-1.5">
            <p class="font-bold text-slate-900"><?= e($order['customer_name']) ?></p>
            <p><?= e($order['customer_phone']) ?></p>
            <p class="text-slate-700 leading-relaxed"><?= nl2br(e($order['delivery_address'])) ?></p>
            <?php if (!empty($order['landmark'])): ?>
              <p class="text-[11px] text-slate-500">Landmark: <?= e($order['landmark']) ?></p>
            <?php endif; ?>
            <p class="font-semibold text-brand-800 pt-1">Area: <?= e($order['area_name']) ?></p>
          </div>
        </div>

        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
          <h3 class="font-serif font-bold text-base text-slate-900 pb-3 border-b border-slate-100">
            Payment Details
          </h3>
          <div class="text-xs text-slate-600 space-y-2">
            <div class="flex justify-between">
              <span>Payment Mode:</span>
              <span class="font-bold text-slate-900"><?= e($order['payment_method']) ?></span>
            </div>
            <div class="flex justify-between">
              <span>Payment Status:</span>
              <span class="font-bold <?= $order['payment_status'] === 'Paid' ? 'text-emerald-700' : 'text-amber-700' ?>">
                <?= e($order['payment_status']) ?>
              </span>
            </div>
            <?php if (!empty($order['razorpay_payment_id'])): ?>
              <div class="flex justify-between">
                <span>Transaction Ref:</span>
                <span class="font-mono text-[11px] text-slate-500"><?= e($order['razorpay_payment_id']) ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <!-- Back to Menu CTA -->
    <div class="text-center pt-10">
      <a href="/products.php" class="inline-flex items-center gap-2 text-xs font-bold text-brand-800 hover:text-brand-900">
        <span>← Order More Fresh Coconuts & Juices</span>
      </a>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
