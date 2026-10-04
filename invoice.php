<?php
/**
 * N.A Fresh Fruits & Coconuts - Official Invoice
 * Real Invoice generated from MySQL order records
 */

require_once __DIR__ . '/config/database.php';

$orderId = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("
    SELECT o.*, i.invoice_number, i.invoice_date, i.items_json, i.customer_details_json
    FROM orders o
    LEFT JOIN invoices i ON o.id = i.order_id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Invoice not found for this order.");
}

// Order Items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$bizName = get_setting('business_name', 'N.A Fresh Fruits & Coconuts');
$bizPhone = get_setting('phone', '+91 98260 12345');
$bizAddress = get_setting('address', 'Shop No. 4, Station Road, Opp. Municipal Garden, Jaora, MP 457226');
$invoiceNumber = $order['invoice_number'] ?: ('INV-' . date('Ymd') . '-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT));
$invoiceDate = $order['invoice_date'] ?: date('Y-m-d', strtotime($order['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice <?= e($invoiceNumber) ?> – <?= e($bizName) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    @media print {
      .no-print { display: none !important; }
      body { background: white !important; }
      .invoice-box { border: none !important; box-shadow: none !important; padding: 0 !important; }
    }
  </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 text-slate-800">
  
  <div class="max-w-3xl mx-auto space-y-4">
    
    <!-- Top Action Buttons (Hidden on Print) -->
    <div class="no-print flex items-center justify-between bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
      <a href="/order-success.php?id=<?= $order['id'] ?>" class="text-xs font-bold text-slate-600 hover:text-slate-900 flex items-center gap-1.5">
        ← Back to Order
      </a>
      <div class="flex items-center gap-2">
        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-xs transition-colors flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          <span>Print / Save as PDF</span>
        </button>
      </div>
    </div>

    <!-- Official Invoice Box -->
    <div class="invoice-box bg-white rounded-3xl p-8 sm:p-12 shadow-sm border border-slate-200 space-y-8">
      
      <!-- Invoice Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-100 pb-8">
        <div>
          <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-xl bg-emerald-800 text-white font-bold flex items-center justify-center text-sm">NA</span>
            <h1 class="font-bold text-2xl text-slate-900"><?= e($bizName) ?></h1>
          </div>
          <p class="text-xs text-slate-500 mt-2 max-w-sm leading-relaxed"><?= e($bizAddress) ?></p>
          <p class="text-xs text-slate-500">Jaora, M.P. • Helpline: <?= e($bizPhone) ?></p>
          <span class="inline-block mt-2 px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-800 text-[10px] font-bold">
            100% Pure Raw Tender Coconuts & Cold-Pressed Juices
          </span>
        </div>

        <div class="text-left sm:text-right space-y-1">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tax Invoice</span>
          <div class="font-mono font-bold text-lg text-slate-900"><?= e($invoiceNumber) ?></div>
          <div class="text-xs text-slate-500">Date: <?= date('d M Y', strtotime($invoiceDate)) ?></div>
          <div class="text-xs text-slate-500">Order ID: <span class="font-mono font-semibold text-slate-700"><?= e($order['order_number']) ?></span></div>
        </div>
      </div>

      <!-- Bill To & Delivery Info -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-xs border-b border-slate-100 pb-8">
        <div>
          <span class="font-bold text-slate-400 uppercase tracking-wider block mb-2">Customer Details</span>
          <div class="font-bold text-sm text-slate-900"><?= e($order['customer_name']) ?></div>
          <div class="text-slate-600 mt-1"><?= e($order['customer_phone']) ?></div>
          <?php if (!empty($order['customer_email'])): ?>
            <div class="text-slate-500"><?= e($order['customer_email']) ?></div>
          <?php endif; ?>
        </div>

        <div>
          <span class="font-bold text-slate-400 uppercase tracking-wider block mb-2">Delivery Destination</span>
          <div class="text-slate-700 leading-relaxed"><?= nl2br(e($order['delivery_address'])) ?></div>
          <?php if (!empty($order['landmark'])): ?>
            <div class="text-slate-500 mt-1">Landmark: <?= e($order['landmark']) ?></div>
          <?php endif; ?>
          <div class="font-semibold text-emerald-800 mt-1">Area: <?= e($order['area_name']) ?>, Jaora</div>
        </div>
      </div>

      <!-- Itemized Table -->
      <div>
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b-2 border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
              <th class="py-3">Drink / Item</th>
              <th class="py-3 text-center">Qty</th>
              <th class="py-3 text-right">Unit Price</th>
              <th class="py-3 text-right">Amount</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($items as $it): ?>
              <tr>
                <td class="py-3.5 pr-2 font-medium text-slate-900">
                  <?= e($it['product_name']) ?>
                </td>
                <td class="py-3.5 px-2 text-center text-slate-700">
                  <?= (int)$it['quantity'] ?>
                </td>
                <td class="py-3.5 px-2 text-right text-slate-700 font-mono">
                  <?= format_price($it['unit_price']) ?>
                </td>
                <td class="py-3.5 pl-2 text-right font-bold text-slate-900 font-mono">
                  <?= format_price($it['total_price']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Subtotals & Grand Total -->
      <div class="pt-4 border-t-2 border-slate-200 flex justify-end">
        <div class="w-full sm:w-72 space-y-2 text-xs">
          <div class="flex justify-between text-slate-600">
            <span>Items Subtotal</span>
            <span class="font-bold text-slate-900 font-mono"><?= format_price($order['subtotal']) ?></span>
          </div>
          <div class="flex justify-between text-slate-600">
            <span>Jaora Local Delivery Fee</span>
            <span class="font-bold text-slate-900 font-mono"><?= format_price($order['delivery_charge']) ?></span>
          </div>
          <?php if ($order['discount_amount'] > 0): ?>
            <div class="flex justify-between text-emerald-700 font-bold">
              <span>Coupon Discount (<?= e($order['coupon_code']) ?>)</span>
              <span class="font-mono">- <?= format_price($order['discount_amount']) ?></span>
            </div>
          <?php endif; ?>
          <div class="pt-3 border-t border-slate-200 flex justify-between text-base font-bold text-slate-900">
            <span>Grand Total</span>
            <span class="text-emerald-900 font-mono text-xl"><?= format_price($order['total_amount']) ?></span>
          </div>
        </div>
      </div>

      <!-- Payment & Footer Info -->
      <div class="pt-8 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-500">
        <div>
          <span class="font-bold text-slate-700 block mb-1">Payment Method</span>
          <p><?= e($order['payment_method']) ?> – Status: <span class="font-bold <?= $order['payment_status'] === 'Paid' ? 'text-emerald-700' : 'text-amber-700' ?>"><?= e($order['payment_status']) ?></span></p>
          <?php if (!empty($order['razorpay_payment_id'])): ?>
            <p class="font-mono text-[11px] text-slate-400">Ref: <?= e($order['razorpay_payment_id']) ?></p>
          <?php endif; ?>
        </div>
        <div class="text-left sm:text-right">
          <p class="font-semibold text-slate-700">Thank you for drinking fresh with N.A Fresh Fruits!</p>
          <p class="text-[11px] text-slate-400 mt-1">This is a computer-generated invoice for your local delivery.</p>
        </div>
      </div>

    </div>

  </div>

</body>
</html>
